<?php

namespace Psc\Code\AST;

use Psc\Data\ArrayCollection;
use Psc\Code\Code;
use IteratorAggregate;

/**
 * Eine Collection von LParametern
 */
class LParameters extends Element implements IteratorAggregate {
  
  /**
   * @var Psc\Data\ArrayCollection
   */
  protected $parameters;
  
  public function __construct($parameters) {
    if ($parameters instanceof ArrayCollection) { // trust the dj
      $this->parameters = $parameters;
    } else {
      if (!Code::isTraversable($parameters)) {
        throw new \InvalidArgumentException('$parameters muss traversable sein.');
      }
      
      $this->parameters = new ArrayCollection();
      foreach ($parameters as $parameter) {
        $this->addParameter($parameter);
      }
    }
  }
  
  /**
   * @param string|LParameter $param name oder parameter
   * @return bool
   */
  public function hasParameter($param) {
    if ($param instanceof LParameter)
      return $this->parameters->contains($param);
    else
      return $this->parameters->containsKey($param);
  }
  
  /**
   * Fügt einen Parameter hinzu
   *
   * @param LParamter $parameter
   * @chainable
   */
  public function addParameter(LParameter $parameter) {
    $this->parameters->set($parameter->getName(), $parameter);
    return $this;
  }
  
  /**
   * @return LParameter
   */
  public function getParameter($name) {
    if (!$this->hasParameter($name)) {
      throw new \Psc\Exception(
        sprintf("LParameters beeinhaltet keinen Parameter mit dem Namen '%s'. Es sind vorhanden: %s", $name, \Psc\FE\Helper::listObjects($this->parameters->toArray(), ',', 'name'))
      );
    }
    
    return $this->parameters->get($name);
  }
  
  /**
   * @param Psc\Data\ArrayCollection $parameters
   */
  public function setParameters(ArrayCollection $parameters) {
    $this->parameters = $parameters;
    return $this;
  }
  
  /**
   * @return Psc\Data\ArrayCollection
   */
  public function getParameters() {
    return $this->parameters;
  }
  
  /**
   * @return Iterator
   */
  public function getIterator() {
    return $this->parameters->getIterator();
  }
}
?>