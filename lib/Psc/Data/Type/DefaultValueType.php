<?php

namespace Psc\Data\Type;

interface DefaultValueType {

  /**
   * Gibt die PHP-Value zurück die eingesetzt werden soll, wenn die Value NULL ist
   *
   * z.b. ArrayType => array()
   * z.B. CollectionType => new \Psc\Data\ArrayCollection()
   * @returm mixed
   */
  public function getDefaultValue();
  
  /**
   * Gibt an ob die DefaultValue eine skalare ist
   * @return bool
   */
  public function hasScalarDefaultValue();
}
?>