<?php

namespace Psc\UI;

/**
 * Ein Button, um eine Lösch-Aktion auszuführen
 *
 * @TODO confirm
 */
class DeleteTabButton extends TabButton {
  
  protected function setUp() {
    $this->setLeftIcon('trash');
    $this->setRightIcon('alert');
  }
  
  protected function doInit() {
    parent::doInit();
    $this->html->addClass('\Psc\button-delete');
  }
}
?>