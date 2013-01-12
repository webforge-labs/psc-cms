<?php

namespace Psc\Doctrine\Functions;

use Doctrine\ORM\Query\Lexer;

abstract class SingleValueDateTime extends \Doctrine\ORM\Query\AST\Functions\FunctionNode {
  
  public $date;
  
  protected $fname;
  
  public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker) {
    return $this->fname.'('.$sqlWalker->walkArithmeticPrimary($this->date).')';
  }
  
  public function parse(\Doctrine\ORM\Query\Parser $parser) {
    $lexer = $parser->getLexer();
    
    $parser->match(Lexer::T_IDENTIFIER);
    $parser->match(Lexer::T_OPEN_PARENTHESIS);
    $this->date = $parser->ArithmeticPrimary();
    $parser->match(Lexer::T_CLOSE_PARENTHESIS);
  }
}
?>