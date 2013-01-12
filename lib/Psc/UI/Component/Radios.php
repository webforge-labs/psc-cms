<?php

namespace Psc\UI\Component;

use Psc\UI\Form AS f;

class Radios extends \Psc\UI\Component\ValuesBase {
  
  public function getInnerHTML() {
    return f::radios($this->getFormLabel(), $this->getFormName(), $this->getValues(), $this->getFormValue());
  }
}
?>