<?php

namespace Psc\CMS;

use Psc\Doctrine\DCPackage;
use Psc\Doctrine\ModelCompiler;

/**
 * 
 */
class ProjectCompiler extends \Psc\System\LoggerObject {
  
  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   * @var Psc\Doctrine\ModelCompiler
   */
  protected $modelCompiler;
  
  /**
   * @var array
   */
  protected $languages;
  
  public function __construct(DCPackage $dc, Array $languages = NULL) {
    $this->dc = $dc;
    $this->setLogger(new \Psc\System\BufferLogger);
    $this->setLanguages($languages ?: \Psc\PSC::getProject()->getConfiguration()->req('languages'));
  }
  
  public function compile() {
    $gClass = new \Psc\Code\Generate\GClass(\Psc\Code\Code::getClass($this));
    $gClass->elevateClass();
    $this->log('compiling ProjectEntities:');
    foreach ($gClass->getMethods() as $method) {
      if (\Psc\Preg::match($method->getName(),'/^compile[a-z0-9A-Z_]+$/') && $method->isPublic()) {
        $this->modelCompiler = NULL; // neuen erzeugen damit flags resetted werden, etc
        $m = $method->getName();
        
        $this->log('  '.$m.':');
        try {
          $out = $this->$m($this->getModelCompiler());
        } catch (\Exception $e) {
          $this->log('    Fehler beim Aufruf von '.$m);
          throw $e;
        }
        
        if ($out instanceof \Webforge\Common\System\File) {
          $this->log('    '.$out.' geschrieben');
        } elseif (is_array($out)) {
          foreach ($out as $file) {
            $this->log('    '.$file.' geschrieben');
          }
        } elseif ($out instanceof \Psc\Doctrine\EntityBuilder) {
          $this->log('    '.$out->getWrittenFile().' geschrieben');
        }
      }
    }
    $this->log('finished.');
    return $this;
  }
  
  public function help() {
    return $this->getModelCompiler()->getClosureHelpers();
  }
  
  /**
   * @return Psc\Doctrine\ModelCompiler
   */
  public function getModelCompiler() {
    if (!isset($this->modelCompiler)) {
      $this->modelCompiler = new ModelCompiler($this->getDoctrinePackage()->getModule());
      $this->modelCompiler->setOverwriteMode(TRUE);
      $this->modelCompiler->setLanguages($this->getLanguages());
    }
    return $this->modelCompiler;
  }
  
  public function getDoctrinePackage() {
    return $this->dc;
  }
  
  /**
   * @param array $languages
   */
  public function setLanguages(Array $languages) {
    $this->languages = $languages;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getLanguages() {
    return $this->languages;
  }
}
?>