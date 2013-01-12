<?php

namespace Psc\Doctrine;

class FieldNotDefinedException extends \Psc\Exception {
  
  public static function entityField($entityClass, $fieldName) {
    return new static(
      sprintf("Das Entity der Klasse '%s' hat kein Feld '%s' in der SetMeta definiert", $entityClass, $fieldName)
    );
  }
}
?>