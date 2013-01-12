<?php

namespace Psc\UI\Component;

use Psc\UI\Form as f;

/**
 * @TODO readonly
 *
 * <input type="text">
 */
class TextField extends \Psc\UI\Component\Base {
  
  public function getInnerHTML() {
    return f::text($this->getFormLabel(), $this->getFormName(), $this->toFormValue());
  }
  
  protected function toFormValue() {
    return $this->getFormValue();
  }
}
?>