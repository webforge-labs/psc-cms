<?php

namespace Psc\Code;

use Psc\PHP\Lexer;
use Webforge\Common\String AS S;

class Extracter extends \Psc\Object {
  
  /**
   * @var Psc\PHP\Lexer
   */
  protected $lexer;
  
  public function __construct(Lexer $lexer = NULL) {
    $this->lexer = $lexer ?: new Lexer();
  }
  
  /**
   * @param string $snippet darin muss function() vorkommen. Die erste Funktion die gefunden wird, wird genommen
   */
  public function extractFunctionBody($snippet) {
    try {
      if (mb_strpos($snippet,'<?php') === FALSE) {
        $snippet = '<?php '.$snippet.' ?>';
      }
      $lines = array();
      
      $this->lexer->setParseWhitespace(TRUE);
      $this->lexer->init($snippet);
      
      $this->lexer->skipUntil('T_FUNCTION');
      if (!$this->lexer->hasNext()) {
        throw new ExtracterException('Keine Funktion im snippet gefunden. '.\Psc\Code\Code::varInfo($snippet));
      }
      $this->lexer->skipUntil(Lexer::T_BRACE_OPEN);
      
      $this->lexer->skipUntilMatching(); // läuft bis zur passenden T_BRACE_CLOSE
      $this->matchCurrent(Lexer::T_BRACE_CLOSE); // ) überspringen (Parameter Ende)
      
      if ($this->lexer->isToken(Lexer::T_SEMICOLON)) { // abstract function hat keinen body
        return $lines;
      }
      
      $this->lexer->skipUntil(Lexer::T_CBRACE_OPEN);
      $this->matchCurrent(Lexer::T_CBRACE_OPEN);
      
      while($this->lexer->isToken(Lexer::T_PLAIN_WHITESPACE))
        $this->lexer->moveNext();
      
      if ($this->lexer->isToken(Lexer::T_COMMENT)) { // inline comment nach der { Klammer
        $lines[] = array(-1, $this->lexer->getToken()->value);
        $this->matchCurrent(Lexer::T_COMMENT);
      }
      
      $this->matchCurrent(Lexer::T_EOL);
      
      /* bei doctrine einrückungsstil kommt hier noch whitespace + nochma eol */
      //$this->lexer->debug('go');
      
      /* jetzt sind wir hinter der öffnenden Function-Body Klammer und wollen den Body bis zur passenden schließenden holen
         wir geben einen array mit 2 Werten zurück.
         0 => int
         1 => string
         
         1 ist der Code der Zeile und 0 ist die Anzahl der Weißzeichen, die vor der Zeile stand (indent)
         -1 indent bedeutet, dass der code inline hinter der { klammer steht (vor dem EOL)
      */
      
      $line = NULL;
      $cbraces = 0;
      $indent = 0; // damit wir wissen wenn etwas nach der { kommt
      $catchIndent = TRUE;
      while ($this->lexer->hasNext()) {
        $token = $this->lexer->getToken();
        
        $catchValue = TRUE;
        switch ($token->type) {
          
          /* eine öffnende cbrace müssen wir zählen, da wir ja wissen wollen, wann die Funktion zu Ende ist */
          case Lexer::T_CBRACE_OPEN:
            $cbraces++;
            break;
          
          /* bei schließender curlyBrace müssen wir schauen, ob wir fertig sind */
          case Lexer::T_CBRACE_CLOSE:
            if ($cbraces === 0) {
              break(2); // ende switch, ende while
            } else {
              $cbraces--;
              break;  
            }
            
          case Lexer::T_EOL:
            /* alte Zeile speichern und neue beginnen */
            $lines[] = array($indent, $line);
            
            $line = NULL;
            $indent = 0;
            $catchIndent = TRUE;
            $catchValue = FALSE;
            break;
            
          /* wenn catchIndent an ist, sind wir am Anfang der Zeile nehmen wir diesen whitespace als indent */
          case Lexer::T_PLAIN_WHITESPACE:
            if ($catchIndent) {
              $indent = mb_strlen($token->value); // hier könnte man noch tabs replacen? 1 tab = 2 white?
              $catchIndent = FALSE;
              $catchValue = FALSE; // indent abschneiden
            }
            break;
        }
        
        if ($catchValue) {
          $line .= $token->value;
        }
        
        if ($catchIndent && $token->type != Lexer::T_EOL) {
          $catchIndent = FALSE; // denn nur der whitespace der direkt nach EOL kommt soll gecaptured werden
        }
        
        $this->lexer->moveNext();
      }
      if (mb_strlen($line) > 0) {
        $lines[] = array($indent,$line);
      }
      
      return $lines;
    
    } catch (ExtracterException $e) {
      $e->context = $snippet;
      throw $e;
    }
  }
  
  public function extract($code, $startLine, $endLine) {
    $source = S::fixEOL($code);
    $source = explode("\n",$source);

    if (!array_key_exists($startLine-1,$source) || !array_key_exists($endLine-1, $source)) {
      throw new \Psc\Exception('In source: '.$file.' gibt es keinen Code von '.$startLine.' bis '.$endLine);
    }
    
    return implode("\n",array_slice($source, $startLine-1, ($endLine-$startLine)+1));
  }
  
  protected function matchCurrent($type) {
    if ($this->lexer->getToken()->type != $type) {
      $this->lexer->debug('parse Error');
      throw new ExtracterException('Parse Error: '.$type.' expected aber. '.$this->lexer->getToken()->type.' gefunden');
    }
    
    return $this->lexer->moveNext();
  }
}
?>