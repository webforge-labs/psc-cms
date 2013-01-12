<?php

namespace Psc\UI\Component;

class FloatField extends \Psc\UI\Component\TextField {
  
  protected $decimals = 2;
  
  protected function toFormValue() {
    return number_format($this->getFormValue(), $this->decimals, ',', ''); // nicht NULL als letzten
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


}
?>