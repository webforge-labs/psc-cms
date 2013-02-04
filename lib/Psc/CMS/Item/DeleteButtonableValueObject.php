<?php

namespace Psc\CMS\Item;

use Psc\CMS\RequestMetaInterface;

class DeleteButtonableValueObject extends TabButtonableValueObject implements DeleteButtonable {
  
  protected $identifier;
  protected $entityName;
  
  /**
   * @var Psc\CMS\RequestMetaInterface
   */
  protected $deleteRequestMeta;
  
  public static function copyFromDeleteButtonable(DeleteButtonable $buttonable) {
    $valueObject = new static();
    
    // ugly, but fast
    $valueObject->setButtonLabel($buttonable->getButtonLabel());
    $valueObject->setFullButtonLabel($buttonable->getFullButtonLabel());
    $valueObject->setButtonLeftIcon($buttonable->getButtonLeftIcon());
    $valueObject->setButtonRightIcon($buttonable->getButtonRightIcon());
    $valueObject->setButtonMode($buttonable->getButtonMode());
    $valueObject->setDeleteRequestMeta($buttonable->getDeleteRequestMeta());
    
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
  
  public function getEntityName() {
    return $this->entityName;
  }
}
?>