<?php

namespace Psc\Symfony;

class Module extends \Psc\CMS\Module {
  
  public function bootstrap($bootFlags = 0x000000) {
    // loaded from composer or something
    
    $this->dispatchBootstrapped();
    return $this;
  }
  
  public function getClassPath() {
    throw new \Psc\Exception('this is deprecated now');
  }
  
  public function getNamespace() {
    return 'Symfony';
  }
  
  public function getModuleDependencies() {
    return array();
  }
}
?>