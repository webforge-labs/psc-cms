<?php

namespace Psc\Code;

class MultiValueCounter {
  
  protected $initCount;
  
  protected $argsCount;
  
  protected $counts;
  
  protected $values;
  
  /**
   * @param int $initCount der Anfängliche Count für jedes Item in der letzten Dimension
   * @param int $argsCount die Größe der Dimension
   */
  public function __construct($initCount = 0, $argsCount = 1) {
    $this->initCount = (int) $initCount;
    $this->argsCount = (int) $argsCount;
    
    $this->counts = array();
  }
  
  /**
   * Gibt die Referenz auf den Counter für die genannte Dimension zurück
   *
   * example:
   * $v =& $mv->c('foo','bar');
   * $v++;
   * $mv->c('foo','bar'); // ist jetzt eins höher
   */
  public function &c() {
    $args = func_get_args();
    if (count($args) > $this->argsCount) {
      $rel = array_pop($args); // rel ist übergeben worden
    } else {
      $rel = 0;
    }
    for ($i=0; $i < $this->argsCount; $i++) {
      $this->values[$i][] = $args[$i];
    }

    $key = $this->getKey($args);
    if (!array_key_exists($key,$this->counts)) {
      $this->counts[$key] = $this->initCount;
    }
    $ref =& $this->counts[$key];
    $ref += $rel;
    return $ref;
  }
  
  protected function getKey(Array $args) {
    if (count($args) != $this->argsCount) {
      throw new Exception('Parameter: '.print_r($args,true).' entspricht nicht den Kriterien');
    }
    return implode('][',$args);
  }
  
  public function getCounts() {
    return $this->counts;
  }
  
  
}

?>