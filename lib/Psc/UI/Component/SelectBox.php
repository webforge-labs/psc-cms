<?php

namespace Psc\UI\Component;

use Psc\UI\fHTML;
use Psc\CMS\Labeler;
use Psc\CMS\LabelerAware;
use Webforge\Types\ValidationType;

class SelectBox extends ValuesBase implements LabelerAware {
  
  protected $labeler;
  
  public function getInnerHTML() {
    $select = fHTML::select($this->getFormName(), $this->getValuesWithLabel(), $this->getFormValue(),
                            array('class'=>array('text','ui-widget-content','ui-corner-all'))
                            );
    \Psc\UI\Form::attachLabel($select,$this->getFormLabel());
    return $select;
  }
  
  protected function getValuesWithLabel() {
    $values = array();
    foreach ($this->values as $value) {
      $values[$value] = $this->labeler->getLabel($value);
    }
    
    return $values;
  }
  
  protected function initValidatorRule() {
    if (isset($this->type) && $this->getType() instanceof ValidationType) {
      $this->validatorRule = $this->getType()->getValidatorRule($this->getTypeRuleMapper());
    } else {
      $this->validatorRule =  new \Psc\Form\ValuesValidatorRule($this->values);
    }
  }
  
  public function dpi(Array $values, Labeler $labeler = NULL) {
    $this->setValues($values);
    $this->labeler = $labeler ?: new Labeler();
    return $this;
  }
  
  /**
   * @param Psc\CMS\Labeler $labeler
   * @chainable
   */
  public function setLabeler(\Psc\CMS\Labeler $labeler) {
    $this->labeler = $labeler;
    return $this;
  }

  /**
   * @return Psc\CMS\Labeler
   */
  public function getLabeler() {
    return $this->labeler;
  }


}
?>