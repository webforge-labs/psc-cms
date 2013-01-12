<?php

namespace Psc\UI\Component;

use Psc\UI\Form as f;

class Checkbox extends \Psc\UI\Component\Base {
  
  public function getInnerHTML() {
    return f::checkbox($this->getFormLabel(), $this->getFormName(), $this->getFormValue());
  }

  protected function initValidatorRule() {
    $this->validatorRule = 'Checkbox';
  }
}
?>