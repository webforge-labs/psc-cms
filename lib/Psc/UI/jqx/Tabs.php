<?php

namespace Psc\UI\jqx;

use Psc\UI\HTML;
use Psc\HTML\Tag;

/**
 * 
 */
class Tabs extends TabsSpecification {
  
  /**
   * @var Tab[]
   */
  protected $tabs = array();
  
  public function __construct(Array $tabs = array()) {
    $this->setTabs($tabs);
    $this->widgetName = 'jqxTabs';
  }
  
  public function setCommonOptions() {
    $this
      ->setAnimationType('fade')
      ->setSelectionTracker(true);
    return $this;
  }
  
  protected function doInit() {
    $this->html = HTML::tag('div', array_merge(array($this->createList()), $this->createContainers()), array('class'=>'webforge-jqx-tabs jqx-rc-all'));
    parent::doInit();
  }
  
  protected function createList() {
    $list = HTML::tag('ul', array());
    
    foreach ($this->tabs as $tab) {
      $list->append(HTML::tag('li', $tab->htmlHead()));
    }
    
    return $list;
  }
  
  protected function createContainers() {
    $containers = array();
    
    foreach ($this->tabs as $tab) {
      $containers[] = HTML::tag('div', $tab->html(), array('class'=>'content-container'));
    }
    
    return $containers;
  }
  
  /**
   * @return array
   */
  public function getTabs() {
    return $this->tabs;
  }
  
  /**
   * @param array $tabs
   */
  public function setTabs(Array $tabs) {
    $this->tabs = $tabs;
    return $this;
  }
  
  /**
   * @param integer $key 0-based
   * @return Psc\UI\jqx\Tab|NULL
   */
  public function getTab($key) {
    return array_key_exists($key, $this->tabs) ? $this->tabs[$key] : NULL;
  }
  
  /**
   * @param Psc\UI\jqx\Tab $tab
   * @chainable
   */
  public function addTab(Tab $tab) {
    $this->tabs[] = $tab;
    return $this;
  }
  
  /**
   * @param Psc\UI\jqx\Tab $tab
   * @chainable
   */
  public function removeTab(Tab $tab) {
    if (($key = array_search($tab, $this->tabs, TRUE)) !== FALSE) {
      array_splice($this->tabs, $key, 1);
    }
    return $this;
  }
  
  /**
   * @param Psc\UI\jqx\Tab $tab
   * @return bool
   */
  public function hasTab(Tab $tab) {
    return in_array($tab, $this->tabs);
  }
}
?>