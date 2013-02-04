<?php

namespace Psc\CMS\Item;

use Psc\CMS\RequestMetaInterface;

class TabButtonableValueObject extends ButtonableValueObject implements TabButtonable {
  
  protected $tabLabel;
  protected $tabRequestMeta;
  
  /**
   * Creates a copy of an tabButtonable
   *
   * use this to have a modified Version of the interface
   * @return TabButtonableValueObject
   */
  public static function copyFromTabButtonable(TabButtonable $tabButtonable) {
    $valueObject = new static();
    
    // ugly, but fast
    $valueObject->setButtonLabel($tabButtonable->getButtonLabel());
    $valueObject->setFullButtonLabel($tabButtonable->getFullButtonLabel());
    $valueObject->setButtonLeftIcon($tabButtonable->getButtonLeftIcon());
    $valueObject->setButtonRightIcon($tabButtonable->getButtonRightIcon());
    $valueObject->setButtonMode($tabButtonable->getButtonMode());
    $valueObject->setTabLabel($tabButtonable->getTabLabel());
    $valueObject->setTabRequestMeta($tabButtonable->getTabRequestMeta());
    
    return $valueObject;
  }
  
  /**
   * @chainable
   */
  public function setTabLabel($label) {
    $this->tabLabel = $label;
    return $this;
  }
  
  public function getTabLabel() {
    return $this->tabLabel;
  }
  
  /**
   * @chainable
   */
  public function setTabRequestMeta(RequestMetaInterface $rm) {
    $this->tabRequestMeta = $rm;
    return $this;
  }

  public function getTabRequestMeta() {
    return $this->tabRequestMeta;
  }
}
?>