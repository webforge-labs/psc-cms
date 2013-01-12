<?php

namespace Psc\Data;

use \Psc\DataInput;

/**
 *
 */
class Storage extends \Psc\SimpleObject {
  
  /**
   * @var Psc\DataInput
   */
  protected $data;
  
  protected $driver;
  
  public function __construct(StorageDriver $driver) {
    $d = array();
    $this->data = new DataInput($d);
    $this->driver = $driver;
  }
  
  /**
   * @chainable
   */
  public function persist() {
    $this->driver->persist($this);
    return $this;
  }
  
  /**
   * @chainable
   */
  public function init() {
    $this->driver->load($this);
    return $this;
  }
  
  /**
   * @return Psc\DataInput
   */
  public function getData() {
    return $this->data;
  }
  
  public function setData(Array $data) {
    $this->data = new DataInput($data);
    return $this;
  }
}

?>