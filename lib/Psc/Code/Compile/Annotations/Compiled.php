<?php

namespace Psc\Code\Compile\Annotations;

/**
 * @Annotation
 */
class Compiled extends \Psc\Code\Annotation implements \Psc\Code\WriteableAnnotation {
  
  public static function create() {
    return new static();
  }
  
  public function getWriteValues() {
    return array();
  }
}
?>