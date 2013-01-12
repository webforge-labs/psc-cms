<?php

namespace Psc\UI\Component;

use Psc\DateTime\DateTime;

class TimeField extends \Psc\UI\Component\TextField {
  
  protected $timeFormat = 'H:i';

  public function getInnerHTML() {
    return parent::getInnerHTML()->addClass('datepicker-time');
  }
  
  protected function toFormValue() {
    if (($date = $this->getFormValue()) instanceof DateTime) {
      return $date->i18n_format($this->timeFormat);
    } else {
      return NULL;
    }
  }
  
  /**
   * @param string $timeFormat
   * @chainable
   */
  public function setTimeFormat($timeFormat) {
    $this->timeFormat = $timeFormat;
    return $this;
  }

  /**
   * @return string
   */
  public function getTimeFormat() {
    return $this->timeFormat;
  }
}
?>