<?php

namespace Psc\Data\Type;

class TypeConversionException extends TypeException {
  
  public static function typeTarget($fromType, $target) {
    return new static(sprintf("Type '%s' konnte nicht zu einem %s umgewandelt werden", $fromType, $target));
  }
}
?>