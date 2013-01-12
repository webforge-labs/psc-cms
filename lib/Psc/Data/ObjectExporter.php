<?php

namespace Psc\Data;

use Psc\Data\Type\Type;

class ObjectExporter extends \Psc\Data\Walker {
  
  public function walk($value, Type $type) {
    if ($value instanceof \Psc\Data\Exportable) {
      return $value->export();
    }
    
    if ($type instanceof \Psc\Data\Type\ObjectType && $value === NULL) {
      return NULL;
    }
    
    return parent::walk($value, $type);
  }

  public function decorateString($string) {
    return $string;
  }

  public function decorateInteger($integer) {
    return $integer;
  }

  public function decorateBoolean($bool) {
    return $bool;
  }

  public function decorateArrayEntry($walkedEntry, $key) {
    return $walkedEntry;
  }
  
  public function decorateArray($walkedEntries, $arrayType) {
    return $walkedEntries;
  }
  
  public function decorateField($field, $walkedValue, $fieldType) {
    return $walkedValue;
  }
  
  public function decorateWalkable($walkable, $walkedFields) {
    return (object) $walkedFields;
  }
}
?>