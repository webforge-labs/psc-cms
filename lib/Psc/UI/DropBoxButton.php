<?php

namespace Psc\UI;

use Psc\CMS\Item\DropBoxButtonable;
use Psc\CMS\Item\DropBoxButtonableValueObject;
use Psc\CMS\Item\JooseBridge;
use Psc\CMS\RequestMetaInterface;

/**
 * Ein Button, der in einer DropBox (intialisiert) angezeigt wird
 *
 */
class DropBoxButton extends TabButton {
  
  public function __construct(DropBoxButtonable $item, JooseBridge $jooseBridge = NULL) {
    parent::__construct($item, $jooseBridge);
  }
  
  protected function setUpItem($item) {
    $this->item = DropBoxButtonableValueObject::copyFromDropBoxButtonable($item);
  }
  
  public function getIdentifier() {
    return $this->item->getIdentifier();
  }

  public function getEntityName() {
    return $this->item->getEntityName();
  }
}
?>