<?php

namespace Psc\CMS\Item;

use Psc\CMS\RequestMetaInterface;

class DropBoxButtonableValueObject extends TabButtonableValueObject implements DropBoxButtonable {
  
  protected $identifier;
  protected $entityName;
  
  public static function copyFromDropBoxButtonable(DropBoxButtonable $buttonable) {
    $valueObject = self::copyFromTabButtonable($buttonable);

    $valueObject->setIdentifier($buttonable->getIdentifier());
    $valueObject->setEntityName($buttonable->getEntityName());
    
    return $valueObject;
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