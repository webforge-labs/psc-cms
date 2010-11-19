<?php

class PHPClass extends PHPElement {
  
  public $name;

  public $extends;

  /**
   * 
   * @var array
   */
  public $properties;

  /**
   * 
   * @var array
   */
  public $constants;

  /**
   * 
   * @var array
   */
  public $methods;

  /**
   * Nummer der Zeile wo die Curly-Brace Offen ist '{'
   * @var int
   */
  public $open;
  /**
   * 
   * @var array
   */
  public $implements = array();

  public function __construct(Array $token) {
    parent::__construct($token);
  }


  public function addProperty(PHPProperty $property) {
    $this->properties[] = $property;
  }

  public function addConstant(PHPClassConstant $constant) {
    $this->constants[] = $constant;
  }

  public function addMethod(PHPMethod $method) {
    $this->methods[] = $method;
  }
  
 
}

?>