<?php

namespace Psc\Data\Type;

class TypeMatcher extends \Psc\SimpleObject {
  
  public function isTypeOf($data, Type $type) {
    // @TODO interface um dem Typ zu erlauben diesen Code selbst zu implementieren
    
    if ($type instanceof StringType) {
      return is_string($data);
    } elseif ($type instanceof ArrayType && !$type->isTyped()) {
      return is_array($data);
    } elseif ($type instanceof ObjectType) {
      if (!is_object($data)) return FALSE;
      return !$type->hasClass() || (($class = $type->getClassFQN()) && $data instanceof $class);
    } elseif ($type instanceof PositiveIntegerType) {
      return is_integer($data) && ($type->hasZero() ? $data >= 0 : $data > 0);
    } elseif ($type instanceof IntegerType) {
      return is_integer($data) && (!$type->hasZero() || $data != 0);
    } else {
      throw new \Psc\Code\NotImplementedException('TypeOf nicht valid für: '.$type->getTypeClass());
    }
  }
}
?>