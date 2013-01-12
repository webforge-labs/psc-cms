<?php

namespace Psc\UI;

use Psc\CMS\Item\TabButtonable;
use Psc\CMS\Item\JooseBridge;

/**
 * Ein TabButton wird als ui-button dargestellt und öffnet einen Tab (jay)
 */
class TabButton extends \Psc\UI\Button implements \Psc\JS\JooseWidget, \Psc\JS\JooseSnippetWidget {
  
  /**
   * @var Psc\CMS\Item\TabButtonable
   */
  protected $item;
  
  public function __construct(TabButtonable $item, JooseBridge $jooseBridge = NULL) {
    $this->item = $item;
    $this->jooseBridge = $jooseBridge ?: new JooseBridge($item);
    parent::__construct(NULL); // kein label für button
    $this->setUp();
  }
  
  protected function doInit() {
    parent::doInit();
    
    // Psc.CMS.Item - construct hinzufügen
    $this->html = $this->jooseBridge->link($this->html)->html();
  }
  
  /**
   * @return string
   */
  public function getLabel() {
    return $this->label ?: $this->item->getButtonLabel();
  }

  protected function setUp() {
    if (($leftIcon = $this->item->getButtonLeftIcon()) !== NULL) {
      $this->setLeftIcon($leftIcon);
    }

    if (($rightIcon = $this->item->getButtonRightIcon()) !== NULL) {
      $this->setRightIcon($rightIcon);
    }
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
}
?>