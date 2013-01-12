<?php

namespace Psc\Doctrine\Functions;

use Doctrine\ORM\Query\Lexer;

class Soundex extends \Doctrine\ORM\Query\AST\Functions\FunctionNode {

  protected $fname = 'SOUNDEX';
  
  public $str;

  public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker) {
    return $this->fname.'('.$sqlWalker->walkStringPrimary($this->str).')';
  }
  
  public function parse(\Doctrine\ORM\Query\Parser $parser) {
    $lexer = $parser->getLexer();
    
    $parser->match(Lexer::T_IDENTIFIER);
    $parser->match(Lexer::T_OPEN_PARENTHESIS);
    $this->str = $parser->StringPrimary();
    $parser->match(Lexer::T_CLOSE_PARENTHESIS);
  }
}
?>