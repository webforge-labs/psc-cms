<?php

namespace Psc\Data;

use Psc\Data\Cache;
use Psc\DataInput;

class ArrayCache extends \Psc\SimpleObject implements \Psc\Data\Cache {
  
  protected $values;
  
  public function __construct() {
    $this->values = new \Psc\DataInput();
  }
  
  /**
   *
   * @chainable
   * @param mixed $key wenn $key === NULL ist muss die implementierung entscheiden ob sie den Schlüssel für ein Objekt selbst versucht zu generieren oder eine Exception schmeisst
   * @return Cache
   */
  public function store($key, $value) {
    if ($key === NULL) throw new \InvalidArgumentException('Schlüssel muss gesetzt sein');
    $this->values->set($key,$value);
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function load($key, &$loaded) {
    try {
      $v = $this->values->get($key, DataInput::THROW_EXCEPTION);
      $loaded = TRUE;
      return $v;
    } catch (\Psc\EmptyDataInputException $e) {
      $loaded = TRUE;
      return NULL;
    } catch (\Psc\DataInputException $e) {
    }
    
    $loaded = FALSE;
  }
  
  /**
   * @return bool
   */
  public function hit($key) {
    return $this->values->contains($key);
  }
  
  public function remove($key) {
    $this->values->remove($key);
    return $this;
  }
}
?>