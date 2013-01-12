<?php

namespace Psc\Data\Type;

use Psc\Code\Generate\GClass;
use Psc\Code\Code;

class Inferrer extends \Psc\SimpleObject {
  
  /**
   * Versucht den Typ von einem PHP-Basis-Datentyp zu erraten
   *
   * @throws Psc\Data\Type\InferException
   */
  public function inferType($value) {
    if (is_string($value)) {
      return new StringType();
    } elseif (is_array($value)) {
      return new ArrayType();
    } elseif (is_object($value)) {
      return $this->inferObjectType($value);
    } elseif (is_integer($value)) {
      return new IntegerType();
    } elseif (is_bool($value)) {
      return new BooleanType();
    } elseif(is_null($value)) {
      return new MixedType();
    }

    $e = new InferException('Typ konnte nicht geraten werden: '.Code::varInfo($value));
    $e->value = $value;
    throw $e;
  }
  
  public function inferObjectType($object) {
    $objectClass = new GClass(Code::getClass($object));
    if ($object instanceof \Doctrine\Common\Collections\Collection) {
      return new CollectionType($objectClass);
    } else {
      return new ObjectType($objectClass);
    }
  }
}
?>