<?php

namespace Psc\UI;

use \Psc\URL\Helper AS URLHelper;

class TabsItem extends \Psc\Object implements \Psc\CMS\TabsContentItem{
  
  protected $type;
  protected $id;
  protected $data;
  protected $label;
  
  protected $todo = 'tabs';
  protected $ctrlTodo = 'item';
  
  public function __construct($type, $id, $label, Array $data = array()) {
    $this->type = $type;
    $this->id = $id;
    $this->label = $label;
    $this->data = $data;
    
    $this->setUp();
  }
  
  public function setUp() {
  }
  
  public function getTabsLabel() {
    return $this->label;
  }
  
  /**
   * $qvars überschreibt die qvars die das Item selbst setzt
   */
  public function getTabsURL(Array $qvars = array()) {
    list ($typeName, $id) = $this->getTabsId();
    
    return URLHelper::getURL('/ajax.php', URLHelper::MY_QUERY_VARS | URLHelper::RELATIVE,
                             array_merge( $this->getURLParams(), $qvars)
                            );
  }
  
  protected function getURLParams() {
    return array('todo'=>$this->todo,
                 'ctrlTodo'=>$this->ctrlTodo,
                 'ajaxData'=>$this->getAjaxParams()
                 );
  }
  
  protected function getAjaxParams() {
    return array(
                 'type'=>$this->type,
                 'identifier'=>$this->id,
                 'data'=>$this->getTabsData()
                 );
  }
  
  /**
   * @return list($typeName, $identifier)
   */
  public function getTabsId() {
    return array($this->type, $this->id, str_replace('.','-',$this->ctrlTodo));
  }
  
  public function getTabsData() {
    return $this->data;
  }

  public function setTabsData(Array $data) {
    $this->data = $data;
    return $this;
  }
  
  public function getHTML() {
    return HTML::Tag('a',HTML::esc($this->getTabsLabel()), array('href'=>$this->getTabsURL(),'class'=>'\Psc\tabs-item'))
        ->guid(implode('-',$this->getTabsId()))
        ->publishGUID()
      ;
  }
  
  public function html() {
    return $this->getHTML()->html();
  }
  
  public function __toString() {
    try {
      return (string) $this->html();
    } catch (\Exception $e) {
      return '<a href="#">'.$this->getTabsLabel().'</a><!-- '.$e.' -->';
    }
  }
}

?>