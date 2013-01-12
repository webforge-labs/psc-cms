<?php

namespace Psc\UI;

use Psc\JS\Helper AS JSHelper;
use Psc\JS\jQuery AS JSjQuery;
use Psc\CMS\Item\RightContentLinkable;
use Psc\CMS\Item\Exporter as ItemExporter;

class DropContentsList extends OptionsObject {
  
  protected $exporter;
  
  protected $openAllIcon = FALSE;
  
  protected $html;
  
  public function __construct() {
    $this->html = HTML::tag('ul', array(), array('class'=>'\Psc\drop-contents-list'));
    $this->exporter = new ItemExporter();
  }
  
  /**
   * @deprecated lieber addLinkable nehmen (for future use)
   */
  public function addTabsItem($item) {
    $this->add($item->getTabsLabel(), $item->getTabsURL(), implode('-',$item->getTabsId()));
    return $this;
  }
  
  public function addLinkable(RightContentLinkable $item) {
    $this->add($item->getLinkLabel(), $item->getTabRequestMeta()->getURL(), $this->exporter->convertToId($item));
    return $this;
  }
  
  public function addCollection($c) {
    foreach($c as $item) {
      $this->addTabsItem($item);
    }
    return $this;
  }
  
  /**
   * @fixme getTabLabel() (aka das Label für den Tab) von $item bei addLinkable wird nicht benutzt
   */
  public function add($label, $link, $id = NULL) {
    // @TODO respect das neue RightContentLinkable und rufe den Tab mit TabLabel auf und so
    
    $a = HTML::tag('a',HTML::esc($label),array('href'=>$link))
      ->addClass('\Psc\drop-content-ajax-call')
    ;
    
    $a->guid($id);
    $a->publishGUID();
    
    $this->html->content[] = HTML::tag('li',$a);
  }
  
  public function html() {
    if (count($this->html->content) === 0) {
      // listen elemente mit 0 elementen sind nicht valide
      return NULL;
    }
    
    return $this->html;
  }
  
  /**
   * @param bool $openAllIcon
   * @chainable
   */
  public function setOpenAllIcon($openAllIcon) {
    $this->openAllIcon = $openAllIcon;
    return $this;
  }

  /**
   * @return bool
   */
  public function getOpenAllIcon() {
    return $this->openAllIcon;
  }

  public function __toString() {
    return (string) $this->html();
  }
}