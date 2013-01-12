<?php

namespace Psc\PHPExcel;

/**
 * Zusätzlich muss noch die PHPExcel.php aus Tiptoi kopiert werden
 *
 * @TODO in phar umwandeln!
 */
class Module extends \Psc\CMS\Module {
  
  public function bootstrap($bootFlags = 0x000000) {
    static $bootstrapped;
    
    if (!$bootstrapped) {
      $bootstrapped = TRUE;
      
      $this->dispatchBootstrapped();
    }

    return $this;
  }
  
  public function getNamespace() {
    return 'PHPExcel_';
  }

  public function getAdditionalClassFiles() {
    // also deprecated: it was used for building phars
    return array(
      $this->getClassPath()->up()->getFile('PHPExcel.php')
    );
  }
  
  public function getClassPath() {
    throw new \Psc\Exception('this is deprecated now');
  }
  
  public function getModuleDependencies() {
    return array();
  }
}
?>