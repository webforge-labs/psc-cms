<?php

namespace Psc\Hitch;

use Psc\PSC;
use Psc\Code\Generate\GClass;

class Module extends \Psc\CMS\Module {
  
  /**
   * Der Namespace für Die Hitch-Objekte (xmlObject)
   *
   * wird z. B. vom Reader bei der instanziierung der default-meta-factory benutzt
   * @var string
   */
  protected $objectsNamespace;
  
  public function bootstrap($bootFlags = 0x000000) {
    $this->dispatchBootstrapped();
    
    return $this;
  }
  
  public function getModuleDependencies() {
    return array('Doctrine');
  }
  
  public function getClassPath() {
    return $this->project->getSrc()->sub('Hitch/lib/Hitch');
  }
  
  public function getNamespace() {
    return 'Hitch';
  }
  
  public function getObjectsNamespace() {
    if (!isset($this->objectsNamespace)) {
      $this->objectsNamespace = PSC::getProject()->getNamespace();
    }
    return $this->objectsNamespace;
  }
  
  
  public function setObjectsNamespace($ns) {
    $this->objectsNamespace = $ns;
    return $this;
  }

  /**
   * @return GClass
   */
  public function expandClassName($className, $possibleNamespace = NULL) {
    if ($className === NULL) return NULL;
    if (!isset($possibleNamespace)) $possibleNamespace = $this->getObjectsNamespace();
    
    if ($className instanceof \Psc\Code\Generate\GClass)
      return $className;
    
    $className = (string) $className;
    if (mb_strpos($className,'\\') !== 0) { // ist kein FQN
      $class = new GClass($className);
      $class->setNamespace($possibleNamespace.$class->getNamespace());
    } else {
      $class = new GClass($className);
    }
    
    return $class;
  }
}
?>