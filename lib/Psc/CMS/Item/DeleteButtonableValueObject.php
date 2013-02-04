<?php

namespace Psc\CMS\Item;

use Psc\CMS\RequestMetaInterface;

class DeleteButtonableValueObject extends ButtonableValueObject implements DeleteButtonable {
  
  protected $identifier;
  protected $entityName;
  
  /**
   * @var Psc\CMS\RequestMetaInterface
   */
  protected $deleteRequestMeta;
  
  public static function copyFromDeleteButtonable(DeleteButtonable $buttonable) {
    $valueObject = self::copyFromButtonable($buttonable);
    
    // ugly, but fast
    $valueObject->setDeleteRequestMeta($buttonable->getDeleteRequestMeta());
    $valueObject->setIdentifier($buttonable->getIdentifier());
    $valueObject->setEntityName($buttonable->getEntityName());
    
    return $valueObject;
  }
  
  /**
   * @chainable
   */
  public function setDeleteRequestMeta(RequestMetaInterface $rm) {
    $this->deleteRequestMeta = $rm;
    return $this;
  }
  
  public function getDeleteRequestMeta() {
    return $this->deleteRequestMeta;
  }
  
  public function getIdentifier() {
    return $this->identifier;
  }
  
  public function setIdentifier($id) {
    $this->identifier = $id;
    return $this;
  }
  
  public function setEntityName($fqn) {
    $this->entityName = $fqn;
    return $this;
  }
  
  public function getEntityName() {
    return $this->entityName;
  }
}
?>