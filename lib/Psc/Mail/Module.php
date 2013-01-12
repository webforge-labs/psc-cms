<?php

namespace Psc\Mail;

class Module extends \Psc\CMS\Module {
  
  public function bootstrap($bootFlags = 0x000000) {
    $this->dispatchBootstrapped();
    
    return $this;
  }
  
  public function getModuleDependencies() {
    return array();
  }
  
  public function getNamespace() {
    return 'Swift';
  }
  
  public function getClassPath() {
    throw new \Psc\Exception('this is deprecated');
  }
}
?>