<?php

namespace Psc\Code\AST;

use IteratorAggregate;
use Psc\Data\ArrayCollection;

/**
 * Eine Collection von Statements
 *
 * ein Statement ist die Grundform eines "Aufrufes" oder einer Variablen Zuweisung, einer Definition o. ä.
 * Der Body einer Funktion z.B. besteht aus statements in serialer order
 */
class LStatements extends ElementCollection implements IteratorAggregate {

  /**
   * @var Doctrine\Common\Collections\Collection<Psc\Code\AST\LStatement>
   */
  protected $statements;
  
  /**
   * @param traversable $statements
   */
  public function __construct($statements = array()) {
    if ($statements instanceof Collection) {
      $this->statements = $statements;
    } else {
      $this->statements = new ArrayCollection(array());
      foreach ($statements as $statement) {
        $this->addStatement($statement);
      }
    }
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Psc\Code\AST\LStatement>
   */
  public function getStatements() {
    return $this->statements;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Psc\Code\AST\LStatement> $statements
   */
  public function setStatements(Collection $statements) {
    $this->statements = $statements;
    return $this;
  }
  
  /**
   * @param integer $key 0-based
   * @return Psc\Code\AST\LStatement|NULL
   */
  public function getStatement($key) {
    return $this->statements->containsKey($key, $this->statements) ? $this->statements->get($key) : NULL;
  }
  
  /**
   * @param Psc\Code\AST\LStatement $statement
   * @chainable
   */
  public function addStatement(LStatement $statement) {
    $this->statements->add($statement);
    return $this;
  }
  
  /**
   * @param Psc\Code\AST\LStatement $statement
   * @chainable
   */
  public function removeStatement(LStatement $statement) {
    if ($this->contains($statement)) {
      $this->removeElement($statement);
    }
    return $this;
  }
  
  /**
   * @param Psc\Code\AST\LStatement $statement
   * @return bool
   */
  public function hasStatement(LStatement $statement) {
    return $this->statements->contains($statement);
  }

  /**
   * @return Iterator
   */
  public function getIterator() {
    return $this->parameters->getIterator();
  }
}
?>