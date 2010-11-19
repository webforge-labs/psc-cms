<?php

class PHPParser extends Object {

  public $lexer;


  /**
   * Eine Liste von PHP Konstrukten aus dem Lexer
   * 
   * die PHP Konstrukte können als größere Tokens verstanden werden.
   * Alle Konstrukte leiten PHPElement ab
   * @var array
   */
  protected $tree = array();


  protected $lastDocComments = array();

  /* Parse Helpers */
  /**
   * Speichert eine Referenz auf die aktuelle Klasse, die gerade offen ist
   * @var PHPClass
   */
  protected $currentClass;

  public function __construct(PHPLexer $lexer) {
    $this->lexer = $lexer;
  }
  

  public function parse() {
    $this->lexer->reset();
    $this->lexer->skipTokens = array('T_WHITESPACE','T_COMMENT');
    $this->tree = array();

    do {
      /* Jede Funktion die in einem Case steht, bewegt den cursor des Lexers weiter
         Je nachdem wieviel, können dies auch mehrere Schritte sein. Danach steht der Cursor
         auf dem nächsten Element
      */
      
      switch($this->lexer->token['type']) { 
        /* 
         * case 'T_WHITESPACE':
         *   $this->whitespace();
         */
        case 'T_DOC_COMMENT':
          $this->docComment();
          continue 2;

        case 'T_CLASS':
          $this->class_();
          continue 2;

        case 'T_CONST':
          $this->classConstant();
          continue 2;

          /* schlüsselwort für properties oder function */
          /* todo: php 4 T_VAR */
        case 'T_PROTECTED':
        case 'T_STATIC':
        case 'T_PUBLIC':
        case 'T_PRIVATE':
          if ($this->classMethod()) continue 2;
          if ($this->classProperty()) continue 2;
          
        case 'T_ABSTRACT':
        case 'T_FINAL':
          if ($this->classMethod()) continue 2;
      }
      
      /* irgendetwas was wir nicht parsen */
      $this->lexer->moveNext();

    } while ($this->lexer->hasNext());
  }

  /**
   * 
   */
  protected function class_() { // der underscore ist nur weil class ein reserviertes wort ist
    if ($this->lexer->isToken('T_CLASS')) {
      $class = new PHPClass($this->lexer->token);

      $this->match('T_STRING'); // der Klassenname
      $class->name = $this->lexer->token['value'];

      if ($this->check('T_EXTENDS')) {
        $this->match('T_STRING');
        $class->setExtends($this->lexer->token['value']);
      }

      if ($this->check('T_IMPLEMENTS')) {
        $this->match('T_STRING');
        $class->implements[] = $this->lexer->token['value'];

        while ($this->check(PHPLexer::T_COMMA)) {
          $this->match('T_STRING');
          $class->implements[] = $this->lexer->token['value'];
        }
      }
      
      $this->match(PHPLexer::T_CBRACE_OPEN);
      $class->open = $this->lexer->token['line'];

      $this->currentClass = $class;

      $this->applyDocComments($class);

      $this->tree[] = $class;
      return;
    }
  }

  /**
   * 
   * Der Cursor steht auf dem ersten protected/private/final/abstract/public - Keyword
   * @return bool
   */
  protected function classProperty() {
    $this->lexer->saveState();

    /* todo: php4 T_VAR */

    /* wir holen uns zuerst die member_modifier und überprüfen dann ob es ein Property ist */
    $member_modifiers = array();
    while (($member_modifier = $this->member_modifier()) instanceof PHPZElement) {
      $member_modifiers[] = $member_modifier->getValue();
    }
    
    if ($this->lexer->isToken('T_VARIABLE')) {
      $property = new PHPProperty($this->lexer->token);
      $property->setMemberModifiers($member_modifiers);
      $property->setName($this->lexer->token['value']);

      if ($this->check(PHPLexer::T_EQUAL)) { /* variable hat einen scalar als default */
        $property->setValue($this->static_scalar());
      }

      /* doc comments */
      $this->applyDocComments($property);

      if (isset($this->currentClass)) $this->currentClass->addProperty($property);
      $this->tree[] = $property;
      return TRUE;
    }

    $this->lexer->loadState();
  }

  /**
   * 
   * Der Cursor steht auf dem protected/private/public - Keyword
   * @return bool
   */
  protected function classMethod() {

    $this->lexer->saveState();

    /* wir holen uns zuerst die member_modifier und überprüfen dann ob es eine method ist */
    $member_modifiers = array();
    while (($member_modifier = $this->member_modifier()) instanceof PHPZElement) {
      $member_modifiers[] = $member_modifier->getValue();
    }
    
    if ($this->lexer->isToken('T_FUNCTION')) {
      $byReference = $this->check(PHPLexer::T_AMPERSAND);
      $this->match('T_STRING');
      $function = new PHPMethod($this->lexer->token);
      $function->setMemberModifiers($member_modifiers);
      $function->setName($this->lexer->token['value']);
      $function->setReturnReference($byReference);

      if ($this->check(PHPLexer::T_BRACE_OPEN)) {
       
        /* parameter sind ja optional */
        if (($parameters = $this->parameter_list()) instanceof PHPZParameterList)
          $function->setParameters($parameters);

        //$this->lexer->debug('parameter_list: schliessende Klammer');

        /* aber die geschlossene Klammer muss es dann geben */
        $this->match(PHPLexer::T_BRACE_CLOSE);
      }

      $this->applyDocComments($function);

      if (isset($this->currentClass)) $this->currentClass->addMethod($function);
      $this->tree[] = $function;
      return TRUE;
    }

    $this->lexer->loadState();
  }

  /**
   * 
   * Member einer Klasse mit CONST eingeleitet (für Klassenname::konstante siehe static_class_constant
   */
  protected function classConstant() {
    if ($this->lexer->isToken('T_CONST')) {
      $constant = new PHPClassConstant($this->lexer->token);
      
      $this->match('T_STRING');
      $constant->name = $this->lexer->token['value'];

      $this->match(PHPLexer::T_EQUAL);
      $constant->value = $this->static_scalar();
      
      $this->applyDocComments($constant);

      $this->match(PHPLexer::T_SEMICOLON);
      $constant->setLastLine($this->lexer->token['line']);

      if (isset($this->currentClass)) $this->currentClass->addConstant($constant);

      $this->tree[] = $constant;
    }
  }

  protected function docComment() {
    if ($this->lexer->isToken('T_DOC_COMMENT')) {
      $this->lastDocComments[] = new PHPDocComment($this->lexer->token);
      $this->lexer->moveNext();
    }
  }


  protected function applyDocComments(PHPElement &$elem) {
    if (count($this->lastDocComments) > 0) {
      $elem->setDocComments($this->lastDocComments);
      $this->lastDocComments = array();
    }
    return $elem;
  }

  /**
   * Erkennt ein Whitespace-Zeichen oder einen Zeilenumbruch
   * 
   */
  protected function whitespace() {
    if ($this->lexer->isToken('T_WHITESPACE')) {

      if ($this->lexer->token['value'] == $this->lexer->eol) {
        $this->tree[] = new PHPWhitespace($this->lexer->token);
      } else {
        $this->tree[] = new PHPNewline($this->lexer->token);
      }

      $this->lexer->moveNext();
    }
  }

  /**
   * 
   * Fügt nichts dem tree hinzu!
   * 
   * setzt den lexer bis auf die schließende Klammer des argumentes
   * @return PHPArray
   */
  protected function array_() {
    if ($this->lexer->isToken('T_ARRAY')) {
      $array = new PHPArray($this->lexer->token);

      /* wenn es eine argument-liste gibt */
      if ($this->check(PHPLexer::T_BRACE_OPEN)) {
        $array->content = $this->getTokensUntilMatching();
      }

      return $array;
    }
  }


  /********** Definitionen aus der Zend Engine **********/



  /**
   * Parsed einen static_scalar und gibt diesen zurück
   * 
   * Achtung: wird nicht zum Baum hinzugefügt, da dies eine Hilfs-Funktion ist
   *
   * static_scalar := common_scalar |	T_STRING | '+' static_scalar | '-' static_scalar | T_ARRAY '(' static_array_pair_list ')' |	static_class_constant
   * die Regeln mit dem '+' und dem '-' verstehe ich nicht.. Sie ergeben auch einen Parseerror beim Testen.
   * @return PHPZElement
   */
  protected function static_scalar() {
    /* wir holen uns zuerst das + oder - vom common_scalar (weiß der heck warum das im php parser so rum ist) */
    $sign = ($this->check(PHPLexer::T_PLUS) || $this->check(PHPLexer::T_MINUS)) ? $this->lexer->token['value'] : NULL;


    if (($static_scalar = $this->common_scalar()) instanceof PHPZElement) {
      $static_scalar->value = $sign.$static_scalar->value; // wir setzten das + oder - einfach davor und gut is
    }
    
    if (
      ($this->check('T_ARRAY') && ($static_scalar = $this->array_()) instanceof PHPElement)
      || ($static_scalar = $this->static_class_constant()) instanceof PHPElement
    ) {
      return $static_scalar;
    }

    /* string unbedingt nach static_class_constant checken */
    if ($this->check('T_STRING'))
      return new PHPZElement($this->lexer->token);
  }

  /**
   * 
   * common_scalar := T_LNUMBER | T_DNUMBER | T_CONSTANT_ENCAPSED_STRING | T_LINE | T_FILE | T_CLASS_C | T_METHOD_C | T_FUNC_C
   * @return PHPZElement
   */
  protected function common_scalar() {
    if ($this->check('T_LNUMBER') 
      || $this->check('T_DNUMBER')
      || $this->check('T_CONSTANT_ENCAPSED_STRING') 
      || $this->check('T_LINE') 
      || $this->check('T_FILE') 
      || $this->check('T_CLASS_C') 
      || $this->check('T_METHOD_C') 
      || $this->check('T_FUNC_C')
      ) {
      return new PHPZElement($this->lexer->token);
    }
  }

  /**
   * 
   * Cursor steht auf dem Token nicht davor!
   */
  protected function member_modifier() {
    if ($this->lexer->isToken('T_PUBLIC') 
      || $this->lexer->isToken('T_PROTECTED') 
      || $this->lexer->isToken('T_PRIVATE') 
      || $this->lexer->isToken('T_STATIC') 
      || $this->lexer->isToken('T_ABSTRACT') 
      || $this->lexer->isToken('T_FINAL') 
      ) {
      $el = new PHPZElement($this->lexer->token);
      $this->lexer->moveNext();
      return $el;
    }
  }

  /**
   * 
   * static_class_constant := T_STRING T_PAAMAYIM_NEKUDOTAYIM T_STRING;
   */
  protected function static_class_constant() {
    $this->lexer->saveState();
    
    if ($this->check('T_STRING')) {
      $classConstant = new PHPStaticClassConstant($this->lexer->token);
      $classConstant->setClassName($this->lexer->token['value']);
      
      if ($this->check('T_PAAMAYIM_NEKUDOTAYIM')) {
        $this->match('T_STRING');
        $classConstant->setName($this->lexer->token['value']);
        
        return $classConstant;
      }
    }

    $this->lexer->loadState();
  }

  /**
   * Parameter Liste einer Funktion/Methode
   * 
   * Cursor steht auf der öffnenden Klammer
   * 
   *  optional_class_type T_VARIABLE	
	 * |	optional_class_type '&' T_VARIABLE			
	 * |	optional_class_type '&' T_VARIABLE '=' static_scalar			
	 * |	optional_class_type T_VARIABLE '=' static_scalar				
	 * |	non_empty_parameter_list ',' optional_class_type T_VARIABLE 	
	 * |	non_empty_parameter_list ',' optional_class_type '&' T_VARIABLE	
	 * |	non_empty_parameter_list ',' optional_class_type '&' T_VARIABLE	 '=' static_scalar 
	 * |	non_empty_parameter_list ',' optional_class_type T_VARIABLE '=' static_scalar 
   *
   * wenn keine Parameter geparsed werden wird NULL zurückgegeben
   * @return NULL|PHPZParameterList
   */
  protected function parameter_list() {
    $parameters = array();
    $token = $this->lexer->token;
    
    do {
      $classType = $this->optional_class_type();
      $byReference = $this->check(PHPLexer::T_AMPERSAND);
      
       /* 
        * T_VARIABLE  muss gegeben sein, wenn nicht, existiert der Parameter nicht
        * da die liste leer sein darf (per Defintion) dürfen wir hier nicht match() nehmen, 
        * weil kein parserror enstehen darf
        */
      if ($this->check('T_VARIABLE')) { 
        
        $parameter = new PHPFunctionParameter($this->lexer->token);
        $parameter->setPassByReference($byReference);
        $parameter->setClassType($classType);
            
        if ($this->check(PHPLexer::T_EQUAL)) {
          $parameter->setDefault($this->static_scalar());
        }

        //$this->lexer->debug('parameter_list');

        $parameters[] = $parameter;
      }
    } while ($this->check(PHPLexer::T_COMMA)); // man könnte hier noch && !isNextToken(PHPLexer::T_BRACE_CLOSE) hinzumachen, ist aber nicht ganz nötig und ich weiß nicht ob kontext-abhängig

    if (count($parameters) > 0) {
      $list = new PHPZParameterList($token);
      $list->setParameters($parameters);
      return $list;
    }
  }

  /**
   * 
   * optional_class_type := T_STRING | T_ARRAY
   */
  protected function optional_class_type() {
    if ($this->check('T_STRING') || $this->check('T_ARRAY')) {
      return new PHPZElement($this->lexer->token);
    }
  }

  /********** Hilfsfuntkionen des Parsers **********/


  protected function match($tokenType) {
    if (!$this->lexer->isNextToken($tokenType)) {
      $this->syntaxError($tokenType);
    }

    $this->lexer->moveNext();
  }

  /**
   * Überprüft ob das nächste Element vom bestimmten Typ ist
   * 
   * Wenn ja wird der Cursor direkt weitergesetzt und die Funktion gibt TRUE zurück
   * @return bool
   */
  protected function check($tokenType) {
    if ($this->lexer->isNextToken($tokenType)) {
      $this->lexer->moveNext();
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Fängt alle Tokens z.b. zwischen zwei Klammern ein
   * 
   * Die aktuelle Position des Lexers muss eine offene Klammer sein.
   * Der Lexer wird dann solange vorgespult bis die matchende Klammer (schließend) gefunden wurde.
   * Danach steht der Lexer auf der abschließenden klammer und im Rückgabewert,
   * sind alle Tokens die zwischen diesen beiden Klammern standen. Die erste Klammer ist ebenfalls nicht im Array enthalten.
   * wird keine Klammer gefunden, sind alle Tokens bis zum Ende des Lexers im Array enthalten.
   * @return array
   */
  protected function getTokensUntilMatching() {
    switch ($this->lexer->token['type']) {
      
      case PHPLexer::T_CBRACE_OPEN: /* geschweifte klammern */
        $openToken = PHPLexer::T_CBRACE_OPEN;
        $closeToken = PHPLexer::T_CBRACE_CLOSE;
        break;
        
        
      case PHPLexer::T_BRACE_OPEN: /* runde klammern */
        $openToken = PHPLexer::T_BRACE_OPEN;
        $closeToken = PHPLexer::T_BRACE_CLOSE;
        break;
        
      default: 
        throw new Exception('aktueller Token ist keine öffnende Klammer oder ein capture-start-Punkt.');
    }
    $this->lexer->moveNext(); // den öffnenen token überspringen

    $tokens = array();
    $stack = array();
    while($this->lexer->hasNext()) {
      if ($this->lexer->token['type'] == $openToken) {
        $stack[] = $this->lexer->token['line'];
      }

      if ($this->lexer->token['type'] == $closeToken) {
        if (count($stack) == 0) { // keine weiteren klammern wurden geöffnet und die klammer schließt sich
          break;
        } else {
          array_pop($stack);
        }
      }
      
      $tokens[] = $this->lexer->token;
      $this->lexer->moveNext();
    }

    return $tokens;
  }

  protected function syntaxError($tokenType) {
    if (is_int($tokenType)) {
      $rflct = new ReflectionClass('PHPLexer');
      $const = array_flip($rflct->getConstants());
      $tokenName = $const[$tokenType];
    } else {
      $tokenName = $tokenType;
    }

    $this->parseError('token: '.$tokenName.' wurde nicht gefunden');
  }

  protected function parseError($string) {
    $kontextLine = array_pop(array_slice(explode("\n",str_replace(array("\r\n","\r"), "\n", $this->lexer->getSource())),$this->lexer->lookahead['line']-1,1));

    throw new PHPParserException($string.' on line: '.$this->lexer->lookahead['line'].' Kontext: '.$kontextLine);
  }

}

class PHPParserException extends PscException {}

?>