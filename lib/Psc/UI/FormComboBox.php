<?php

namespace Psc\UI;

use \Psc\CMS\TabsContentItem,
    \stdClass,
    \Psc\Code\Code
;

class FormComboBox extends HTMLTag {
  
  protected $name;
  protected $label;
  
  /**
   * itemType: wird als type: in den Ajax-Request für den TabsContentItemController übergeben (beim Autocomplete)
   * itemData: wird als data: in den Ajax-Request für den TabsContentItemController übergeben (beim Autocomplete)
   *          itemdata wird auch beim event selected angefügt, wenn item.data (von den autocomplete-items) nicht gesetzt ist
   *          somit kann man itemData als "globalItemData" sehen.
   *
   * selectMode: bool   wenn gesetzt wird beim auswählen der text in der comboBox mit der Value des Menu-Items ersetzt
   *
   * delay        Optionen für AC
   * minLength    Optionen für AC
   *
   */
  protected $widgetOptions;
  
  protected $init = FALSE;
  
  protected $initialText = 'Begriff hier eingeben, um die Auswahl einzuschränken.';
  protected $ajax;
  
  protected $loadedItems; // vorgeladene Items (werden durch Ajax-Requests ersetzt)
  protected $assignedItem; // das in der ComboBox ausgewählte Item, ist dies nicht gesetzt wird intialText genommen
  
  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  public function __construct($label, $name = NULL, $loadedItems = NULL, $assignedItem = NULL) {
    $this->loadedItems = $loadedItems;
    $this->assignedItem = $assignedItem;
    $this->widgetOptions = new stdClass;
    
    parent::__construct('input', NULL, array('type'=>'text'));
    
    $this->label = $label;
    $this->name = $name;
  }
  
  protected function guessItemInfo() {
    if (isset($this->assignedItem) && $this->assignedItem instanceof \Psc\CMS\TabsContentItem) {
      list ($type,$id) = $this->assignedItem->getTabsId();
      $this->widgetOptions->itemType = $type;
      $this->widgetOptions->itemData = $this->assignedItem->getTabsData();
    }
  }
  
  public function init() {
    if (!$this->init) {
      
      if (!isset($this->widgetOptions->itemType)) {
        $this->guessItemInfo();
      }

      /* input fertig machen */
      list($this->label,$this->name,$id) = Form::expand($this->label, $this->name, NULL);
      $this->setAttribute('name',\Psc\Form\HTML::getName($this->name));
      $this->guid($id);
      Form::attachLabel($this,$this->label);
      
      if (!isset($this->widgetOptions->itemType))
        throw new \Psc\Exception('widgetOptions->itemType muss für init()/html() gesetzt sein.');
      
      if (!isset($this->widgetOptions->width)) $this->widgetOptions->width = '90%';
      
      
      if (isset($this->loadedItems) && count($this->loadedItems) > 0) {
        $loadedItems = array();
        foreach ($this->loadedItems as $item) {
          $loadedItems[] = $this->exportItem($item);
        }
        $this->widgetOptions->loadedItems = $loadedItems;
        $this->setAjax(FALSE);
      }

      if (isset($this->assignedItem)) {
        $this->widgetOptions->assignedItem = $this->exportItem($this->assignedItem);
        $this->widgetOptions->initialText = $this->widgetOptions->assignedItem['label'];
      } elseif($this->ajax == FALSE) {
        $this->widgetOptions->initialText = '';
      } else {
        $this->widgetOptions->initialText = $this->initialText;
      }
      
      jQuery::widget($this, 'comboBox',(array) $this->widgetOptions);
      
      $this->init = TRUE;
    }
    return $this;
  }
  
  public function html() {
    $this->init();

    return parent::html();
  }


  protected function exportItem(TabsContentItem $item) {
    list($type,$identifier) = $item->getTabsId();
        
    $jsItem = array(
      'type'=>$type,
      'identifier'=>$identifier,
      'data'=>$item->getTabsData(),
      'value'=>$identifier, // ac eigenschaften
      'label'=>$item->getTabsLabel(), // ac eigenschaften
    );
    
    return $jsItem;
  }
  
  public function setAjax($bool) {
    $this->widgetOptions->ajax = ($bool == true);
    $this->ajax = FALSE;
    return $this;
  }
}
?>