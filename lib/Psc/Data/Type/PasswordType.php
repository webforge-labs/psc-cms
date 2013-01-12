<?php

namespace Psc\Data\Type;

class PasswordType extends \Psc\Data\Type\StringType {
  
  protected $algorithm;
  protected $minLength;
  
  public function __construct($algorithm = 'md5', $minLength = 5) {
    $this->algorithm = $algorithm;
    $this->minLength = $minLength;
    parent::__construct();
  }

  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('PasswordFields');
  }

  public function getValidatorRule(TypeRuleMapper $mapper) {
    return $mapper->createRule('Password', array($this->minLength));
  }
  
  /**
   * @param int $minLength
   * @chainable
   */
  public function setMinLength($minLength) {
    $this->minLength = $minLength;
    return $this;
  }

  /**
   * @return int
   */
  public function getMinLength() {
    return $this->minLength;
  }


  
  /**
   * @param string $algorithm
   * @chainable
   */
  public function setAlgorithm($algorithm) {
    $this->algorithm = $algorithm;
    return $this;
  }

  /**
   * @return string
   */
  public function getAlgorithm() {
    return $this->algorithm;
  }


}
?>