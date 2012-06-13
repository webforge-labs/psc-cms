<?php

namespace Psc\System\Console;

use Psc\CMS\EntityMeta;

/**
 * 
 */
class CompiledNormalClass2 {
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  public function __construct(EntityMeta $entityMeta, $name) {
    $this->name = $name;
    $this->setEntityMeta($entityMeta);
  }
  
  /**
   * @param string $name
   * @chainable
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getName() {
    return $this->name;
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