<?php

namespace Psc\CMS;

use Psc\Doctrine\DCPackage;

/**
 * The Design Package helps for common tasks doing in the frontend for example in a controller
 *
 * 
 */
class DesignPackage {
  
  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   *
   */
  public function __construct(DCPackage $dc) {
    $this->dc = $dc;
  }
  
  public function createAction($entityOrMeta, $verb, $subResource = NULL) {
    if (is_string($entityOrMeta)) {
      $entityOrMeta = $this->dc->getEntityMeta($this->dc->expandEntityName($entityOrMeta));
    }
    
    return new Action($entityOrMeta, $verb, $subResource);
  }
  
  public function createTabButton($label, $action) {
    
  }
  
  
  /**
   * @param Psc\Doctrine\DCPackage $dc
   */
  public function setDoctrinePackage(DCPackage $dc) {
    $this->dc = $dc;
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage() {
    return $this->dc;
  }
}
?>