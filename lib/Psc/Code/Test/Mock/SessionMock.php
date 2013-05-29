<?php

namespace Psc\Code\Test\Mock;

use Psc\DataInput;

class SessionMock extends \Psc\Session\Session {
  
  protected $sessionArray;
  
  public function init() {
    if (!$this->init) {
      
      $this->sessionArray = array();
      $this->data = new DataInput($this->sessionArray, DataInput::TYPE_ARRAY);
    
      $this->init = TRUE;
    }
    return $this;
  }
  
  public function destroy() {
    $this->sessionArray = array();
    return $this;
  }
}
