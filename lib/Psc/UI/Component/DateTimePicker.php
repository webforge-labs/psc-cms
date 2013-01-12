<?php

namespace Psc\UI\Component;

use Psc\HTML\HTML;
use Psc\Form\DateTimeValidatorRule;
use Psc\Form\ComponentsValidator;
use Psc\UI\Component\CompositeComponent;
use Psc\DateTime\DateTime;

/**
 */
class DateTimePicker extends \Psc\UI\Component\JavascriptBase implements CompositeComponent, JavaScriptComponent {

  /**
   * Das Feld für die Uhrzeit
   * 
   * @var Psc\UI\Component\TimeField
   */
  protected $timeField;

  /**
   * Psc\UI\Component\DatePicker
   */
  protected $datePicker;
  
  /**
   * The Boolean decider on validation, if the component should have a time value
   *
   * for example. We got a calendar application which allows to set "whole day event", so that the time is not necessary
   * 
   * @var string the name of the Psc\UI\Component\CheckBox component (nicer: BooleanComponent)
   */
  protected $timeIsOptionalComponentName;
  
  /**
   * Cache for the retrieved Option
   * 
   * @var bool
   */
  protected $timeIsOptional;
  
  protected function setUp() {
    $this->datePicker = new DatePicker();
    $this->timeField = new TimeField();
    parent::setUp();
  }
  
  public function onValidation(ComponentsValidator $validator) {
    $this->initTimeIsOptional($validator);
    $this->getValidatorRule()->setTimeIsOptional($this->timeIsOptional);
  }
  
  protected function initTimeIsOptional(ComponentsValidator $validator) {
    if (!isset($this->timeIsOptionalComponentName)) {
      $this->timeIsOptional = FALSE;
    } else {
      $booleanComponent = $validator->getComponentByFormName($this->timeIsOptionalComponentName);
      $validator->validateComponent($booleanComponent);
      $this->timeIsOptional = $booleanComponent->getFormValue();
    }
  }
  
  public function getValidatorRule() {
    if (!isset($this->validatorRule)) {
      $this->validatorRule = new DateTimeValidatorRule();
    }
    
    return $this->validatorRule;
  }
  
  public function getJavascript() {
    return $this->createJooseSnippet(
      'Psc.UI.DateTimePicker',
      (object) array(
        'dateFormat'=>$this->datePicker->getDateFormat(),
        'value'=>(($value = $this->getFormValue()) instanceof DateTime ? $value->export() : NULL),
        'timeFormat'=>'h:i',
        'widget'=>$this->widgetSelector()
      )
    );
  }
  
  protected function doInit() {
    $this->datePicker->init();
    $this->timeField->init();
    
    parent::doInit();
  }
  
  public function getInnerHTML() {
    return HTML::tag('span', (object) array('date'=>$this->datePicker->getInnerHTML(),
                                            'time'=>$this->timeField->getInnerHTML(),
                                           )
                    );
  }

  public function setFormLabel($label) {
    parent::setFormLabel($label);
    $this->datePicker->setFormLabel($label);
    return $this;
  }

  public function setFormValue($value) {
    parent::setFormValue($value);
    $this->datePicker->setFormValue($value);
    $this->timeField->setFormValue($value);
    return $this;
  }
  
  public function setFormName($name) {
    parent::setFormName($name);
    $this->setSubFormName($this->datePicker, 'date');
    $this->setSubFormName($this->timeField, 'time');
    return $this;
  }
  
  public function setDateFormat($format) {
    $this->datePicker->setDateFormat($format);
    return $this;
  }
  
  public function getDateFormat() {
    return $this->datePicker->getDateFormat();
  }

  public function getTimeFormat() {
    return $this->timeField->getDateFormat($format);
  }

  /**
   * @param string $timeFormat
   * @chainable
   */
  public function setTimeFormat($timeFormat) {
    $this->timeField->setTimeFormat($timeFormat);
    return $this;
  }
  
  public function setTimeIsOptionalComponentName($name) {
    $this->timeIsOptionalComponentName = $name;
    return $this;
  }
}
?>