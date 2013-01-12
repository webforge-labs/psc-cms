<?php

namespace Psc\PHP;

use Psc\Exception;
use Psc\Preg;
use Psc\String AS S;

/**
 * 
 * Invariante des Lexers:
 * - Token ist immer der aktuelle Token. $this->lookahead ist immer der nächste Token (muss nicht $this->position +1 sein )
 * 
 * Ein Tokentyp ist entweder self::LITERAL oder eine der T_* Werte von PHP (siehe doku für token_name()) diese T_* werte werden als strings übergeben
 * 
 * $lexer = new Lexer(file_get_contents('D:\stuff\Webseiten\psc-cms\Umsetzung\base\src\psc\class\PHP\Lexer.php'));
   while ($lexer->hasNext()) {
     var_dump($lexer->token);
     $lexer->moveNext();
   }
 */
class Lexer extends \Psc\Object {
  
  const LITERAL = 'LITERAL';

  // wenn hier konstanten hinzugefügt werden, diese bei scan auch hinzufügen
  const T_COMMA = 101; 
  const T_DOT = 102;
  const T_CBRACE_OPEN = 103;  // {
  const T_CBRACE_CLOSE = 104; // }
  const T_EQUAL = 105; // =
  const T_PLUS = 106; // +
  const T_MINUS = 109; // -
  const T_BRACE_OPEN = 107; // (
  const T_BRACE_CLOSE = 108; // )
  const T_AMPERSAND = 110; // &
  const T_SEMICOLON = 111; // ;
  
  const T_PLAIN_WHITESPACE = 201; // \s, \t usw
  const T_EOL = 202; // \n
  
  const T_COMMENT = 'T_COMMENT';

  protected $tokens = array();

  /**
   * 
   * @var array die Werte sind die Typen der Tokens die übersprungen werden sollen
   */
  public $skipTokens = array();

  /**
   * 
   * $token = array('value'=>string|int,  kann auch eine klassen-konstante T_ sein für die literals
   *                 'type'=>string http://de2.php.net/manual/de/tokens.php,
   *                 'line'=>int, die erste Zeile ist 1
   *                 );
   * @var array ein Token
   */  
  public $token;

  /**
   * 
   * @var array ein Token
   */  
  public $lookahead;

  /**
   * 
   * @var string
   */
  public $eol = "\n";
  
  /**
   * Wenn gesetzt wird whitespaces in whitespaces + EOL geparsed
   *
   * dafür muss EOL = "\n" sein
   */
  protected $parseWhitespace = FALSE;

  /**
   * Die aktuelle Position des aktuellen Tokens im Array 
   * 
   * @var int 0 basiserend
   */
  protected $position = 0;

  /**
   * Zustand
   * 
   * kann mit saveState() gefüllt werden. wird dann mit loadState() wieder geleert
   * @var array
   */  
  protected $savedState;

  /**
   * 
   * @var string
   */  
  protected $source;

  
  public function __construct($source = NULL) {
    if (isset($source))
      $this->init($source);
  }

  /**
   * Initialisiert den Lexer
   * 
   * der Sourcecode wird gelesen und in tokens übersetzt
   * nach diesem Befehl ist der erste token in $this->token gesetzt
   */
  public function init($source) {
    $this->source = $source; // für debug
    $this->scan($source);
    $this->reset();
  }

  /**
   * 
   * @param string $source der PHP Code als String
   */
  protected function scan($source) {
    $source = str_replace(array("\r\n","\r"), "\n", $source);
    if ($this->eol != "\n") $source = str_replace("\n",$this->eol,$source);

    /* token get all ist php intern */
    $currentLine = 1;
    foreach (token_get_all($source) as $token) {

      if (is_string($token)) {
        
        switch($token) {
          default: 
            $type = self::LITERAL;
            break;
          case ',':
            $type = self::T_COMMA;
            break;
          case '.':
            $type = self::T_DOT;
            break;

          case '{':
            $type = self::T_CBRACE_OPEN;
            break;
          case '}':
            $type = self::T_CBRACE_CLOSE;
            break;
          case '(':
            $type = self::T_BRACE_OPEN;
            break;
          case ')':
            $type = self::T_BRACE_CLOSE;
            break;

          case '=':
            $type = self::T_EQUAL;
            break;
          case '&':
            $type = self::T_AMPERSAND;
            break;
        case ';':
            $type = self::T_SEMICOLON;
            break;

          case '+':
            $type = self::T_PLUS;
            break;
          case '-':
            $type = self::T_MINUS;
            break;

        }

        $t = array(
          'value'=>$token,
          'type'=>$type,
          'line'=>$currentLine,
        );

      } else {
        $t = array(
          'value'=>$token[1],
          'type'=>token_name($token[0]),
          'line'=>$token[2],
        );

        /* fix */
        if ($t['type'] == 'T_DOUBLE_COLON')
          $t['type'] = 'T_PAAMAYIM_NEKUDOTAYIM';
          
        $currentLine = $t['line'];
      }

      /* whitespace analyisieren */
      if ($t['type'] == 'T_WHITESPACE' && $this->parseWhitespace) {
        Preg::match($t['value'],'/([^\n]+|\n)/g',$matches);
        foreach ($matches as $m) { // das geht kaputt mit $this->eol anders als "\n"
          list ($NULL,$white) = $m;
          if ($white === "\n") {
            $currentLine++;
            $type = self::T_EOL;
          } else {
            $type = self::T_PLAIN_WHITESPACE;
          }
          
          $this->tokens[] = array(
            'value'=>$white,
            'type'=>$type,
            'line'=>$currentLine
          );
        }
      } elseif ($t['type'] == 'T_COMMENT' && $this->parseWhitespace) {
        // EOL abschneiden und als einzelnes T_EOL dahinter machen
        if (S::endsWith($t['value'], $this->eol)) {
          $t['value'] = mb_substr($t['value'],0,-1);
          
          $this->tokens[] = $t; // comment hinzufügen
          
          $currentLine++;
          $this->tokens[] = array(
            'value'=>"\n",
            'type'=>self::T_EOL,
            'line'=>$currentLine
          );
        } else {
          $this->tokens[] = $t; // comment hinzufügen, auch wenn er kein EOL hata
        }
      } else {
        if (trim($t['value']) == '' && mb_strpos($t['value'],$this->eol) !== FALSE) // leer und mit umbruch
          $currentLine += mb_substr_count($t['value'],$this->eol);
      
        $this->tokens[] = $t;
      }
    }
    
    return $this;
  }
  
  /**
   * @return \stdClass
   */
  public function getToken() {
    return (object) $this->token;
  }
  
  /**
   * @return \stdClass
   */
  public function getLookahead() {
    return (object) $this->lookahead;
  }

  /**
   * Bewegt den Cursor auf die den nächsten Token
   * 
   * Der nächste Token wird in lookahead gespeichert (letzten Token ist dieser dann === NULL)
   * der aktuelle Token ist in token
   * Gibt TRUE zurueck wenn noch ein weiteres mal moveNext() ausgeführt werden kann
   * @return bool
   */
  public function moveNext() {
    $this->token = NULL;
    /* wir könnten zwar auch token = lookahead  machen (aus performance gründen), dies ist aber nicht so
       gut wenn skipTokens umgesetzt werden, oder die Funktion aus reset() aufgerufen wird. Bei reset() müssten wir
       dann diesen Programmcode hier duplizieren, was nicht so schön ist */
    while (array_key_exists(++$this->position, $this->tokens)) {
      if (!in_array($this->tokens[$this->position]['type'], $this->skipTokens)) {
        $this->token = $this->tokens[$this->position];
        break;
      } 
    }
    
    /* lookahead auf das nächstes element setzen, welches nicht übersprungen werden soll */
    $this->lookahead = NULL;
    $lookaheadPosition = $this->position; // wir machen eine kopie, damit wir beim nächsten moveNext nicht vom lookahead ausgehen
    while (array_key_exists(++$lookaheadPosition, $this->tokens)) {
      if (!in_array($this->tokens[$lookaheadPosition]['type'], $this->skipTokens)) {
        $this->lookahead = $this->tokens[$lookaheadPosition];
        break;
      } 
    }
        
    return $this->hasNext();
  }

  /**
   * Gibt TRUE zurueck wenn noch ein weiteres mal moveNext() ausgeführt werden kann
   * 
   * @return bool
   */
  public function hasNext() {
    return $this->lookahead != NULL;
  }

  /**
   * Speichert den atkuellen Zustand des Lexers
   * 
   * kann mit loadState() wieder zurückgeladen werden. Dies kann z.B. dafür benutzt 
   * werden kurzfristig den Cursor zu verschieben und sich die alte Position zu merken
   */
  public function saveState() {
    $this->savedState = array($this->position,$this->token,$this->lookahead);
  }

  public function loadState() {
    if (is_array($this->savedState)) {
      list($this->position,$this->token,$this->lookahead) = $this->savedState;
      $this->savedState = NULL;
    } else {
      throw new Exception('Es wurde kein Status mit saveState() gespeichert der jetzt geladen werden kann');
    }
  }

  /**
   * Bewegt den Cursor auf den nächsten Token mit angegebem Typ
   * 
   * @chainable
   */
  public function skipUntil($type) {
    while (!$this->isToken($type) && $this->hasNext()) {
      $this->moveNext();
    }

    return $this;
  }


  /**
   * @retun array die Tokens dazwischen
   */
  public function skipUntilMatching() {
    switch ($this->getToken()->type) {
      
      case Lexer::T_CBRACE_OPEN: /* geschweifte klammern */
        $openToken = Lexer::T_CBRACE_OPEN;
        $closeToken = Lexer::T_CBRACE_CLOSE;
        break;
        
        
      case Lexer::T_BRACE_OPEN: /* runde klammern */
        $openToken = Lexer::T_BRACE_OPEN;
        $closeToken = Lexer::T_BRACE_CLOSE;
        break;
        
      default: 
        throw new Exception('aktueller Token ist keine öffnende Klammer oder ein capture-start-Punkt.');
    }
    $this->moveNext(); // den öffnenen token überspringen

    $tokens = array();
    $stack = array();
    while($this->hasNext()) {
      $token = $this->getToken();
      
      if ($token->type == $openToken) {
        $stack[] = $token->line;
      }

      if ($token->type == $closeToken) {
        if (count($stack) == 0) { // keine weiteren klammern wurden geöffnet und die klammer schließt sich
          break;
        } else {
          array_pop($stack);
        }
      }
      
      $tokens[] = $token;
      $this->moveNext();
    }

    return $tokens;
  }

  /**
   * Überspringt die nächsten Tokens mit dem angegebenen Typ
   * 
   * Bewegt den Cursor nach vorn
   */
  public function skip($tokenType) {
    while ($this->isNextToken($tokenType) && $this->hasNext()) {
      $this->moveNext();
    }
  }

  /**
   * 
   * @param int|string $tokenType
   * @return bool
   */
  public function isToken($tokenType) {
    return isset($this->token) && $this->token['type'] === $tokenType;
  }

  /**
   * 
   * @param int|string $tokenType
   * @return bool
   */
  public function isNextToken($tokenType) {
    return isset($this->lookahead) && $this->lookahead['type'] === $tokenType;
  }

  public function debug($label) {
    
    echo "Lexer Debug for label:".$label.":\n";
    echo 'token: ';
    var_dump($this->token)."\n\n";
    echo 'lookahead: ';
    var_dump($this->lookahead)."\n\n";
    echo "Ende Lexer Debug for label: ".$label."\n\n";
  }


  /**
   * Setzt die Position auf den Anfang zurück
   */
  public function reset() {
    $this->token = NULL;
    $this->lookahead = NULL;

    $this->position = -1;
    $this->moveNext();
  }
}
?>