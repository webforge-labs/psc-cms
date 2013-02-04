<?php

namespace Psc\UI;

use Psc\CMS\Item\DeleteButtonable;
use Psc\CMS\Item\DeleteButtonableValueObject;
use Psc\CMS\Item\JooseBridge;
use Psc\CMS\RequestMetaInterface;

/**
 * Ein Button, um eine Lösch-Aktion auszuführen
 *
 */
class DeleteTabButton extends TabButton {
  
  public function __construct(DeleteButtonable $item, JooseBridge $jooseBridge = NULL) {
    parent::__construct($item, $jooseBridge);
  }
  
  protected function setUpItem($item) {
    $this->item = DeleteButtonableValueObject::copyFromDeleteButtonable($item);
  }
  
  protected function setUp() {
    $this->setLeftIcon('trash');
    $this->setRightIcon('alert');
  }
  
  protected function doInit() {
    parent::doInit();
    $this->html->addClass('\Psc\button-delete');
  }
  
  /**
   * @return Psc\CMS\RequestMetaInterface
   */
  public function getDeleteRequestMeta() {
    return $this->item->getDeleteRequestMeta();
  }
}
?>