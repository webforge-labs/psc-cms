<?php

namespace Psc\Code\AST;

use Psc\Data\ArrayCollection;

/**
 * L für Language (wir können sie ja leider nicht Function nennen)
 *
 * die $statements bilden Body
 */
class LFunction extends Element {
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var Psc\Code\AST\LParameters
   */
  protected $parameters;
  
  /**
   * @var Psc\Code\AST\LStatements
   */
  protected $statements;
  
  public function __construct($name, LParameters $parameters, LStatements $body = NULL) {
    $this->setName($name);
    $this->setParameters($parameters);
    $this->setStatements($body ?: new LStatements());
  }
  
  /**
   * @param string $name
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * @param Psc\Code\AST\LParameters $parameters
   */
  public function setParameters(LParameters $parameters) {
    $this->parameters = $parameters;
    return $this;
  }
  
  /**
   * @return Psc\Code\AST\LParameters
   */
  public function getParameters() {
    return $this->parameters;
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