<?php

namespace Psc\CMS\Item;

use Psc\CMS\RequestMetaInterface;

class TabButtonableValueObject implements TabButtonable {
  
  protected $buttonLabel;
  protected $fullButtonLabel;
  protected $buttonLeftIcon;
  protected $buttonRightIcon;
  protected $buttonMode;
  
  protected $tabLabel;
  protected $tabRequestMeta;
  
  public function __construct() {
    $this->buttonMode = self::CLICK | self::DRAG;
  }
  
  /**
   * Creates a copy of an tabButtonable
   *
   * use this to have a modified Version of the interface
   * @return TabButtonableValueObject
   */
  public static function copyFrom(TabButtonable $tabButtonable) {
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
  
  public function getButtonLabel() {
    return $this->buttonLabel;
  }
  
  public function setButtonLabel($label) {
    $this->buttonLabel = $label;
    return $this;
  }
  
  /**
   * @chainable
   */
  public function setFullButtonLabel($label) {
    $this->fullButtonLabel = $label;
    return $this;
  }
  
  public function getFullButtonLabel() {
    return $this->fullButtonLabel;
  }

  /**
   * @chainable
   */
  public function setButtonLeftIcon($icon) {
    $this->buttonLeftIcon = $icon;
  }

  public function getButtonLeftIcon() {
    return $this->buttonLeftIcon;
  }

  /**
   * @chainable
   */
  public function setButtonRightIcon($icon) {
    $this->buttonRightIcon = $icon;
    return $this;
  }
  
  public function getButtonRightIcon() {
    return $this->buttonRightIcon;
  }

  /**
   * @chainable
   */
  public function setButtonMode($bitmap) {
    $this->buttonMode = $bitmap;
    return $this;
  }

  public function getButtonMode() {
    return $this->buttonMode;
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