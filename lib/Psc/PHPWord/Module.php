<?php

namespace Psc\PHPWord;

class Module extends \Psc\CMS\Module {
  
  public function bootstrap($bootFlags = 0x000000) {
    if (!defined('PHPWORD_BASE_PATH')) {
      define('PHPWORD_BASE_PATH',(string) $this->getClassPath()->up());

      $this->dispatchBootstrapped();
    }
    
    return $this;
  }

  public function getNamespace() {
    return 'PHPWord_';
  }
  
  public function getAdditionalClassFiles() {
    return array(
      $this->getClassPath()->up()->getFile('PHPWord.php')
    );
  }
  
  public function getAdditionalPharFiles() {
    $cp = $this->getClassPath();
    $files = array();
    foreach ($cp->sub('_staticDocParts')->getFiles() as $file) {
      $files[ $file->getURL($cp->up()) ] = $file;
    }
    return $files;
  }
  
  public function getClassPath() {
    return $this->project->getSrc()->sub('PHPWord/PHPWord/');
  }
  
  public function getModuleDependencies() {
    return array();
  }
}
?>