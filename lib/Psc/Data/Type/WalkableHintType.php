<?php

namespace Psc\Data\Type;

interface WalkableHintType {
  
  /**
   * Muss einen String zurückgeben der eine Walker::walk$hint() Funktion ist
   *
   * @return string
   */
  public function getWalkableHint();
}
?>