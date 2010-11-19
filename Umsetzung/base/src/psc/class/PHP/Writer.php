<?php

class PHPWriter extends Object {

  const M_PUBLIC = 'public';
  const M_PRIVATE = 'private';
  const M_PROTECTED = 'protected';
  const M_FINAL = 'final';
  const M_STATIC = 'static';
  const M_ABSTRACT = 'abstract';

  /**
   * Code der modifiziert wird
   * 
   * @see prepareSource()
   * @var array der PHP Code der modifiziert wird in Zeilen aufgesplittet
   */
  protected $source;

  /**
   * 
   * @var string
   */
  protected $className;

  /**
   * 
   * @var PHPParser
   */
  protected $parser;

  public function __construct($source) {
    $this->prepareSource($source);

    $this->parser = new PHPParser(new PHPLexer($source));
    $this->parser->parse();
  }


  /**
   * Fügt eine Methode der Klasse hinzu
   * 
   * @param string $name
   * @param string $body ohne die geschweiften klammern
   * @param string $parameters
   * @param const[] $modifiers
   */
  public function addMethod($name, $body, $parameters, Array $modifiers = NULL) {

    foreach ($this->parser->tree as $item) {
      if ($item instanceof PHPClass)
        $class = $item;
      
      if ($item instanceof PHPMethod) {
        $method = $item;
        break;
      }
    }

    if (!isset($class))
      throw new Exception ('Es wurde keine Klasse im Sourcecode gefunden um ein neues Property hinzufügen zu können.');

    $after = NULL;
    $code = NULL;
    if (isset($method)) {
      $line = (($dc = $method->getFirstDocComment()) instanceof PHPDocComment) ? $dc->getFirstLine() : $method->getFirstLine();
      $after = "\n";
      $code .= "\n";
      $mode = 'before';
    } else {
      $line = $class->getOpen();
      $mode = 'after';
    }

    /* wir fügen die Methode bei $line ein */
    $code .= '  '.$this->modifierString($modifiers).'function '.$name.'('.$parameters.') {'."\n";
    $code .= $body;
    $code .= "\n  }\n";
    $code .= $after;
    
    $this->writeLine($code,$line,$mode);
    
  }

  public function addProperty($name, $data, Array $modifiers = NULL) {
    /* wir suchen uns einen Platz für unser neues property im source */
    foreach($this->parser->tree as $item) {
      if ($item instanceof PHPClass)
        $class = $item;

      if ($item instanceof PHPClassConstant) {
        $constant = $item;
      }

      if ($item instanceof PHPProperty) {
        $lastProperty = $item;
      }

      if ($item instanceof PHPMethod) {
        $method = $item;
        break;
      }
    }

    if (!isset($class))
      throw new Exception ('Es wurde keine Klasse im Sourcecode gefunden um ein neues Property hinzufügen zu können.');

    $code = NULL;
    $after = NULL;
    $mode = 'before';
    if (isset($lastProperty)) {
      $code .= "\n";
      $line = $lastProperty->getLastLine()+1;
    } elseif (isset($method)) {
      $line = (($dc = $method->getFirstDocComment()) instanceof PHPDocComment) ? $dc->getFirstLine() : $method->getFirstLine();
      $after = "\n";
    } elseif (isset($constant)) {
      $line = (($dc = $constant->getFirstDocComment()) instanceof PHPDocComment) ? $dc->getLastLine() : $constant->getLastLine();
      $code .= "\n";
      $mode = 'after';
    } else {
      $line = $class->getOpen();
      $mode = 'after';
    }

    /* wir fügen das Property bei $line ein */
    $code .= '  '.$this->modifierString($modifiers).self::variable('$'.$name, $data).$after;
    
    $this->writeLine($code,$line,$mode);
  }
  
  public function addConst($name, $value) {
    $code = self::variable('CONST '.$name, $value);
    
  }

  /**
   * Gibt den Code zum Setzen einer Variablen zurück
   * @param string $varCode der Teil der Deklaration der vor dem = steht
   * @param mixed $data die Daten die in die Variable geschrieben werden sollen
   */
  public static function variable($varCode, $data) {
    return $varCode.' = '.var_export($data,TRUE).';';
  }


  /**
   * 
   * hat am Ende ein Weißzeichen wenn es modifiers gibt
   * @return string
   */
  protected function modifierString(Array $modifiers = NULL) {
    $modifiers = (array) $modifiers;

    $ms = array(self::M_PUBLIC, self::M_PRIVATE, self::M_PROTECTED, self::M_FINAL, self::M_ABSTRACT, self::M_STATIC);
    
    $ret = NULL;
    foreach ($modifiers as $modifier) {
      if (!in_array($modifier,$ms)) {
        throw new Exception('Falscher Parameter: '.Code::varInfo($modifier).' nicht erlaubt als Modifier. PHPWriter - Konstanten nur erlaubt.');
      } else {
        $ret .= $modifier.' ';
      }
    }

    return $ret;
  }


  /**
   * 
   * replace:
   *  Die Zeile wird mit dem Code ersetzt
   * before: 
   *  Die Zeile mit der Nummer $line wird hinter den Code geschoben (Der Code wird vor der Zeile $line eingefügt)
   * after: 
   *  Der Code wird hinter der Zeile $line eingefügt
   * @param string $code PHP Code
   * @param int $line die Zeile an der der Code eingefügt werden soll
   * @param string $mode 'before','after','replace'
   */
  public function writeLine($code, $line, $mode = NULL) {
    $mode = Code::dvalue($mode, 'before', 'replace', 'after');

    if ($mode == 'replace') {
      $off1 = 0;
      $off2 = +1;
    } 
    
    if($mode == 'before') {
      $off1 = 0;
      $off2 = 0;
    }

    if($mode == 'after') {
      $off1 = +1;
      $off2 = +1;
    }

    $src = array_merge(
      array_slice($this->source,0,$line-1+$off1), // schneidet bis eine zeile vor $line wen $off1 = 0
      array($code),
      array_slice($this->source,$line-1+$off2) // schneidet ab der zeile $line wenn $off2 = 0
    );
    $this->source = $src;
  }

  /**
   * Setzt den internen Array für den Sourcecode
   * 
   * Zerteilt den PHPCode in Zeilen
   * @var string
   */
  protected function prepareSource($source) {
    $source = str_replace(array("\r\n","\r"), "\n", $source);

    $this->source = explode("\n",$source);
  }

  public function export() {
    return implode("\n",$this->source);
  }
}

?>