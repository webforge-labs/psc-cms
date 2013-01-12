<?php

namespace Psc\System\Console;

use Psc\CMS\EntityMeta;

/**
 * 
 */
class CompiledPropertyAddTestClass extends PropertyAddBaseTestClass {
  
  /**
   * @var Traversable
   */
  protected $assignedValues;
  
  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  public function __construct($valuesCollection, $flags = 0, EntityMeta $entityMeta) {
    $this->assignedValues = $valuesCollection; 
    $this->setEntityMeta($entityMeta);
  }
  
  protected function doInit() {
  }
  
  public function toBeImplemented() {
    
  }
  
  /**
   * @param Psc\CMS\EntityMeta $entityMeta
   */
  public function setEntityMeta(EntityMeta $entityMeta) {
    $this->entityMeta = $entityMeta;
    return $this;
  }
  
  /**
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta() {
    return $this->entityMeta;
  }
}
?>