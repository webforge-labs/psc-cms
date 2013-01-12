<?php

namespace Psc\Code\Test;

use \Psc\GlobalInput
  ;

class GlobalDataLogger extends DataLogger {
  
  protected $types = array('GET','POST');

  public function log() {
    $data = array();
    foreach ($this->types as $type) {
      $data[$type] = GlobalInput::instance($type)->getData();
    }
    
    return $this->addLogData($data);
  }
}

?>