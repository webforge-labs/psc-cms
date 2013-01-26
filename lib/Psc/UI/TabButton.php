<?php

namespace Psc\UI;

use Psc\CMS\Item\TabButtonable;
use Psc\CMS\Item\TabButtonableValueObject;
use Psc\CMS\Item\JooseBridge;

/**
 * Ein TabButton wird als ui-button dargestellt und öffnet einen Tab (jay)
 */
class TabButton extends \Psc\UI\Button implements \Psc\UI\TabButtonInterface, \Psc\JS\JooseWidget, \Psc\JS\JooseSnippetWidget {
  
  /**
   * @var Psc\CMS\Item\TabButtonableValueObject
   */
  protected $item;

  public function __construct(TabButtonable $item, JooseBridge $jooseBridge = NULL) {
    $this->item = TabButtonableValueObject::copyFrom($item);
    
    parent::__construct($this->item->getButtonLabel()); // kein label für button
    
    if ($jooseBridge) {
      $this->jooseBridge = $jooseBridge;
      $this->jooseBridge->setItem($this->item);
    } else {
      $this->jooseBridge = new JooseBridge($this->item);
    }
    $this->setUp();
  }
  
  protected function doInit() {
    parent::doInit();
    
    $bridgedTag = $this->jooseBridge->link($this->html);
    $this->html = $bridgedTag->html();
  }

  protected function setUp() {
    if (($leftIcon = $this->item->getButtonLeftIcon()) !== NULL) {
      $this->setLeftIcon($leftIcon);
    }

    if (($rightIcon = $this->item->getButtonRightIcon()) !== NULL) {
      $this->setRightIcon($rightIcon);
    }
  }
  
  /**
   * @return string
   */
  public function getLabel() {
    return $this->item->getButtonLabel();
  }
  
  public function setLabel($label) {
    parent::setLabel($label);
    $this->item->setButtonLabel($label);
    return $this;
  }
  
  /**
   * @chainable
   */
  public function onlyClickable() {
    return $this->setMode(TabButtonable::CLICK);
  }

  /**
   * @chainable
   */
  public function onlyDraggable() {
    return $this->setMode(TabButtonable::DRAG);
  }

  /**
   * @chainable
   */
  public function clickableAndDraggable() {
    return $this->setMode(TabButtonable::CLICK | TabButtonable::DRAG);
  }
  
  protected function setMode($bitmap) {
    $this->item->setButtonMode($bitmap);
    return $this;
  }
  
  // joose interfaces
  public function getJoose() {
    return $this->jooseBridge;
  }

 /**
   * @return Psc\JS\JooseSnippet
   */
  public function getJooseSnippet() {
    return $this->jooseBridge->getJooseSnippet();
  }
  
  public function disableAutoLoad() {
    $this->jooseBridge->disableAutoLoad();
    return $this;
  }
}
?>