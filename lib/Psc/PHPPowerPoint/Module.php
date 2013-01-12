<?php

namespace Psc\PHPPowerPoint;

class Module extends \Psc\CMS\Module {
  
  public function bootstrap($bootFlags = 0x000000) {
    //if (!defined('PHPWORD_BASE_PATH')) {
    //  require_once $this->getPharFile();
    //
    //  define('PHPWORD_BASE_PATH',(string) $this->getPharResource());
    //
    //  $this->dispatchBootstrapped();
    //}
    //
    require_once $this->getPharFile();
    
    $this->dispatchBootstrapped();
    
    return $this;
  }

  public function getNamespace() {
    return 'PHPPowerPoint_';
  }
  
  public function getAdditionalClassFiles() {
    return array(
      $this->getClassPath()->up()->getFile('PHPPowerPoint.php')
    );
  }
  
  //public function getAdditionalPharFiles() {
  //  $cp = $this->getClassPath();
  //  $files = array();
  //  foreach ($cp->sub('_staticDocParts')->getFiles() as $file) {
  //    $files[ $file->getURL($cp->up()) ] = $file;
  //  }
  //  return $files;
  //}
  
  public function getClassPath() {
    return $this->project->getSrc()->sub('PHPPowerPoint/PHPPowerPoint/');
  }
  
  public function getModuleDependencies() {
    return array();
  }
}
?>