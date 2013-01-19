<?php

namespace Psc\UI;

use Psc\CMS\Item\TabButtonable;
use Psc\CMS\Item\JooseBridge;

/**
 * Ein TabButton wird als ui-button dargestellt und öffnet einen Tab (jay)
 */
class TabButton extends \Psc\UI\Button implements TabButtonable, \Psc\JS\JooseWidget, \Psc\JS\JooseSnippetWidget {
  
  /**
   * @var Psc\CMS\Item\TabButtonable
   */
  protected $item;
  
  public function __construct(TabButtonable $item, JooseBridge $jooseBridge = NULL) {
    $this->item = $item;
    $this->jooseBridge = $jooseBridge ?: new JooseBridge($this);
    parent::__construct(NULL); // kein label für button
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
    return $this->label ?: $this->item->getButtonLabel();
  }
  
  public function getJoose() {
    return $this->jooseBridge;
  }
  
  public function disableAutoLoad() {
    $this->jooseBridge->disableAutoLoad();
    return $this;
  }

  /**
   * @return Psc\JS\JooseSnippet
   */
  public function getJooseSnippet() {
    return $this->jooseBridge->getJooseSnippet();
  }

  /**
   * @return string
   */
  public function getButtonLabel() {
    return $this->item->getButtonLabel();
  }
  
  /**
   * @return string
   */
  public function getFullButtonLabel() {
    return $this->item->getFullButtonLabel();
  }
  
  /**
   * Gibt entweder einen Icon namen (ui-icon-$name) oder NULL zurück
   *
   * @return string
   */
  public function getButtonLeftIcon() {
    return $this->item->getButtonLeftIcon();
  }
  
  /**
   * Gibt entweder einen Icon namen (ui-icon-$name) oder NULL zurück
   *
   * @return string
   */
  public function getButtonRightIcon() {
    return $this->item->getButtonRightIcon();
  }

  /**
   * @return bitmap self::CLICK|self::DRAG
   */
  public function getButtonMode() {
    return $this->item->getButtonMode();
  }
  
  /**
   * @return string
   */
  public function getTabLabel() {
    return $this->item->getTabLabel();
  }
  
  /**
   * @return Psc\CMS\RequestMeta
   */
  public function getTabRequestMeta() {
    return $this->item->getTabRequestMeta();
  }
}
?>