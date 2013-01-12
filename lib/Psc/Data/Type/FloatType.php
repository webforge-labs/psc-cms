<?php

namespace Psc\Data\Type;

class FloatType extends \Psc\Data\Type\Type implements \Psc\Doctrine\ExportableType, \Psc\Data\Type\MappedComponentType, \Psc\Data\Type\ValidationType {

  /**
   * @var bool
   */
  protected $zero = TRUE;

  /**
   * Die Anzahl der Stellen mit dem der Float formatiert werden soll beider Ausgabe
   * 
   * ka ob 2 ein schöner default ist
   * @var int
   */
  protected $decimals = 2;
  
  /**
   * @return bool
   */
  public function hasZero() {
    return $this->zero;
  }
  
  /**
   * @param bool $zero
   * @chainable
   */
  public function setZero($zero) {
    $this->zero = $zero == TRUE;
    return $this;
  }
  
  /**
   * @param int $decimals
   * @chainable
   */
  public function setDecimals($decimals) {
    $this->decimals = $decimals;
    return $this;
  }

  /**
   * @return int
   */
  public function getDecimals() {
    return $this->decimals;
  }

  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    $floatField = $componentMapper->createComponent('FloatField');
    $floatField->setDecimals($this->decimals);
    return $floatField;
  }
  
  public function getDoctrineExportType() {
    return 'float';
  }
  
  public function getPHPType() {
    return 'float';
  }

  public function getValidatorRule(TypeRuleMapper $mapper) {
    return $mapper->createRule('Float', array($this->hasZero()));
  }

  public function __toString() {
    return '[Type:'.$this->getTypeClass()->getFQN().' Zero:'.($this->hasZero() ? 'true' : 'false').']';
  }
}
?>