<?php

namespace Psc\UI\Component;

use Psc\JS\JooseSnippet;

class DatePicker extends \Psc\UI\Component\JavaScriptBase implements JavaScriptComponent {

  /**
   * Das Feld für das Datum
   * 
   * @var Psc\UI\Component\TextField
   */
  protected $dateField;
  
  /**
   *
   * @var string
   */
  protected $dateFormat = 'd.m.Y';


  protected function setUp() {
    $this->dateField = new TextField();
    parent::setUp();
  }
  
  public function getJavascript() {
    return $this->createJooseSnippet(
      'Psc.UI.DatePicker',
      (object) array(
        'dateFormat'=>'d.m.Y',
        'widget'=>$this->findInJSComponent('input.datepicker-date')
      )
    );
  }
  
  public function initValidatorRule() {
    $this->validatorRule = new \Psc\Form\DateValidatorRule($this->dateFormat);
  }
  
  protected function doInit() {
    $this->dateField->init();
    parent::doInit();
  }
  
  protected function initDateFieldValue() {
    if (($date = $this->getFormValue()) != NULL) {
      $this->dateField->setFormValue($date->i18n_format($this->getDateFormat()));
    }
  }
  
  public function getInnerHTML() {
    return $this->dateField->getInnerHTML()->addClass('datepicker-date');
  }

  public function setFormLabel($label) {
    parent::setFormLabel($label);
    $this->dateField->setFormLabel($label);
    return $this;
  }
  
  public function setFormValue($value) {
    parent::setFormValue($value);
    $this->initDateFieldValue(); //re-set
    return $this;
  }
  
  
  public function getFormValue() {
    /* @TODO hier könnten wir auch das string-format wieder umwandeln
       das ist aber die Frage, wer hier die Form-Daten auswertet - diese Componente wohl eher nicht
    */
    return parent::getFormValue();
  }

  public function setFormName($name) {
    parent::setFormName($name);
    $this->dateField->setFormName($name);
    return $this;
  }
  
  /**
   * @param string $dateFormat
   * @chainable
   */
  public function setDateFormat($dateFormat) {
    $this->dateFormat = $dateFormat;
    
    if (isset($this->validatorRule)) {
      $this->validatorRule->setFormat($this->dateFormat);
    }
    
    $this->initDateFieldValue(); //re-set
    return $this;
  }

  /**
   * @return string
   */
  public function getDateFormat() {
    return $this->dateFormat;
  }
}
?>