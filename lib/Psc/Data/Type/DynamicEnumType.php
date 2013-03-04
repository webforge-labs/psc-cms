<?php

namespace Psc\Data\Type;

use Webforge\Common\ArrayUtil AS A;

abstract class DynamicEnumType extends EnumType {
  
  public function __construct(Type $valueType) {
    parent::__construct($valueType, array());
    $this->values = NULL; // die setzen wir selbst in dynamic values (kompatibler wäre hier array())
  }
  
  /**
   * @var array
   */
  protected $valueLabels = array();
  
  /**
   * Gibt entweder einen Array mit $value => $label oder einen array mit $value zurück
   *
   * wenn gesetzt (array nicht numerisch) werden die Labels der Componente dann vorgeschlagen, diese kann diese aber mit dem Labeler aber überschreiben
   *
   * @return assocArray|numericArray wenn assoc dann $value=>$label sonst list($value1, $value2, $value3) usw
   */
  abstract protected function getDynamicValues();
  
  public function getValues() {
    if (!isset($this->values)) {
      $dynamics = $this->getDynamicValues();
      // performance: kein check ob array
      
      if (A::isNumeric($dynamics)) {
        $this->values = $dynamics;
        $this->valueLabels = array();
      } else {
        $this->values = array_keys($dynamics);
        $this->valueLabels = $dynamics;
      }
    }
    
    return $this->values;
  }
  
  /**
   * Gibt die Component mit den Labels der Dynamic Values zurück (wenn gesetzt)
   *
   * Wird decoraten die Component wenn sie LabelerAware ist mit unseren Labels die wir aus den getDynamicValues erhalten, wenn der zurückgegebene array assoziativ ist
   * @return Psc\CMS\Component
   */
  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    $component = parent::getMappedComponent($componentMapper); // ruft this getValues() auf, somit ist sichergestellt, dass valueLabels gesetzt ist
    
    if ($component instanceof \Psc\CMS\LabelerAware) {
      $labeler = $component->getLabeler();
      foreach ($this->valueLabels as $value => $label) {
        $labeler->addLabelMapping($value, $label);
      }
    }
    
    return $component;
  }
}
?>