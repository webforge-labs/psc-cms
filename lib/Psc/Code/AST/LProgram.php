<?php

namespace Psc\Code\AST;

use Doctrine\Common\Collections\Collection;
use Psc\Data\ArrayCollection;

/**
 * Ein Program ist der Start einer Reihe von Statements
 */
class LProgram extends Element {
  
  /**
   * @var Psc\Code\AST\LStatements
   */
  protected $statements;
  
  public function __construct(LStatements $statements) {
    $this->setStatements($statements);
  }
  
  /**
   * @param Psc\Code\AST\LStatements $statements
   */
  public function setStatements(LStatements $statements) {
    $this->statements = $statements;
    return $this;
  }
  
  /**
   * @return Psc\Code\AST\LStatements
   */
  public function getStatements() {
    return $this->statements;
  }
}
?>