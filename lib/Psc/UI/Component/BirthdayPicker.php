<?php

namespace Psc\UI\Component;

use Psc\Data\Set;

/**
 * Schöner wäre die yearKnown Checkbox hier direkt dabei zu haben um nicht die Abhängigkeiten in onValidation() benutzen zu müssen
 */
class BirthdayPicker extends \Psc\UI\Component\DatePicker {
  
  /**
   * Wenn FALSE wird das Jahr nicht mit angezeigt
   * 
   */
  protected $yearKnown = TRUE;
  
  protected function doInit() {
    parent::doInit();
    $this->initDateFormat();
  }
  
  
  protected function initDateFormat() {
    if ($this->yearKnown)
      return $this->setDateFormat('d.m.Y');
    else
      return $this->setDateFormat('d.m.');
  }
  
  public function onValidation(\Psc\Form\ComponentsValidator $validator) {
    // dependency zu yearKnown
    $this->setYearKnown(
      $validator->validateComponent($validator->getComponentByFormName('yearKnown'))
        ->getFormValue()
    );
  }
  
  /**
   * @param bool $yearKnown
   * @chainable
   */
  public function setYearKnown($yearKnown) {
    $this->yearKnown = $yearKnown;
    $this->initDateFormat(); // re-set
    return $this;
  }

  /**
   * @return bool
   */
  public function getYearKnown() {
    return $this->yearKnown;
  }
}
?>