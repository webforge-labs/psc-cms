<?php

namespace Psc\Code\AST;

use Psc\Data\ArrayCollection;
use Psc\Code\Code;
use IteratorAggregate;

/**
 * Arguments werden in einem Funktionsaufruf (noch nicht implementiert) oder einem Constructor-Aufruf (ConstructExpression) benutzt
 */
class LArguments extends Element implements IteratorAggregate {
  
  /**
   * @var array
   */
  protected $arguments = array();

  public function __construct($arguments) {
    if ($arguments instanceof ArrayCollection) { // trust the dj
      $this->arguments = $arguments;
    } else {
      if (!Code::isTraversable($arguments)) {
        throw new \InvalidArgumentException('$arguments muss traversable sein.');
      }
      
      $this->arguments = new ArrayCollection();
      foreach ($arguments as $argument) {
        $this->addArgument($argument);
      }
    }
  }
  
  /**
   * @return ArrayCollection
   */
  public function getArguments() {
    return $this->arguments;
  }
  
  /**
   * @param array $arguments
   */
  public function setArguments(ArrayCollection $arguments) {
    $this->arguments = $arguments;
    return $this;
  }
  
  /**
   * @param Psc\Code\AST\LArgument $argument
   * @chainable
   */
  public function addArgument(LArgument $argument) {
    $this->arguments->add($argument);
    return $this;
  }
  
  /**
   * @param Psc\Code\AST\LArgument $argument
   * @chainable
   */
  public function removeArgument(LArgument $argument) {
    if ($this->arguments->contains($argument)) {
      $this->arguments->removeElement($argument);
    }
    return $this;
  }
  
  /**
   * @param integer $index 0-based
   * @return Psc\Code\AST\LArgument|NULL
   */
  public function getArgument($index) {
    return $this->arguments->containsKey($index) ? $this->arguments[$index] : NULL;
  }
  
  /**
   * @param Psc\Code\AST\LArgument $argument
   * @return bool
   */
  public function hasArgument(LArgument $argument) {
    return $this->arguments->contains($argument);
  }

  /*
   * @return Iterator
   */
  public function getIterator() {
    return $this->arguments->getIterator();
  }
}
?>