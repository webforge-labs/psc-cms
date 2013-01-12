<?php

namespace Psc\UI;

use \Psc\JS\Lambda,
    \Psc\JS\Code AS JSCode,
    \Psc\Code\Code,
    \stdClass
;

class FormDropBox extends HTMLTag {
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var string
   */
  protected $label;
  
  /**
   * @var array
   */
  protected $sortableOptions;

  protected $droppableOptions;
  
  protected $widgetOptions;
  
  /**
   * Wird TRUE wenn die Buttons dem HTML Element hinzugefügt wurden (in Content)
   *
   * die Konvertierung passiert mindestens bei html(), kann aber auch schon vorher sein
   */
  protected $buttonsConverted = FALSE;
  
  /**
   * Die beeinhalteten Buttons im Container
   *
   * @var TabsContentItem[]
   */
  protected $buttons;
  
  protected $verticalButtons = FALSE;
  
  
  public function __construct($label, $name, $buttons = NULL) {
    $this->droppableOptions = new \stdClass;
    $this->widgetOptions =  new stdClass();
    $this->name = $name;
    $this->label = $label;
    $this->buttons = Code::castArray($buttons);
    parent::__construct('div', array());
    
    $this->addClass('\Psc\drop-box')
      ->addClass('ui-widget-content')
      ->addClass('ui-corner-all')
      ->setStyle('min-height','110px')
    ;
    
    if ($this->label != NULL) {
      Form::attachLabel($this, $this->label);
    }
    
    $this->sortableOptions = array(
                                   'cancel'=>false,
                                   'appendTo'=>'parent',
                                   'start'=>new Lambda("function (event,ui) {
                                                       ui.helper.data('pscsort',true);
                                             }"),
                                   'update'=>new Lambda('function (event, ui) {
                                                    if (ui.sender == null) { // nur die empfangene drop-box triggered
                                                      $(this).trigger(\'unsaved\');
                                                    }
                                             }'),
                 );
    $this->sortableOptions = (object) $this->sortableOptions;
  }
  
  public function setLabel($label)  {
    $this->label = $this->contentTemplate->label->content = $label;
    return $this;
  }
  
  public function convertButtons() {
    if (!$this->buttonsConverted) {
      
      $x = 0;
      foreach ($this->buttons as $button) {
        if ($button instanceof \Psc\CMS\TabsContentItem) {
          $item = $button;
          
          if ($x == 0) { // first,once
            if (!isset($this->itemType)) list($this->itemType,$null) = $item->getTabsId();
          }
          
          $button = new TabsButtonItem($item);
            $button->getHTML()
              ->addClass('assigned-item')
            ;
          
          $this->content[] = $button;
          
        } else {
          throw new \Psc\Exception('Diesen Typ von Button kenne ich noch nicht: '.Code::varInfo($button));
        }
        $x++;
      }
      
      $this->buttonsConverted = TRUE;
    }
  }
  
  public function html() {
    
    // erst converten weil dies notfalls itemType noch setzt
    $this->convertButtons();
    $this->getWidgetOptions()->droppableOptions = $this->droppableOptions;
    
    jQuery::widget($this, 'sortable', (array) $this->sortableOptions);
    jQuery::widget($this, 'dropBox', (array) $this->getWidgetOptions());
    
    return parent::html();
  }
  
  public function getWidgetOptions() {
    if (!isset($this->widgetOptions->formName)) $this->widgetOptions->formName = \Psc\Form\HTML::getName($this->name);
    if (!isset($this->widgetOptions->multiple)) $this->widgetOptions->multiple = TRUE;
    return $this->widgetOptions;
  }
  
  public function setItemType($type) {
    $this->widgetOptions->itemType = $type;
    return $this;
  }

  public function setMultiple($bool) {
    $this->widgetOptions->multiple = ($bool == TRUE);
    return $this;
  }
  
  public function getMultiple() {
    return (bool) $this->widgetOptions->multiple;
  }
  
  public function setVerticalButtons($bool) {
    $this->widgetOptions->verticalButtons = ($bool == true);
    $this->verticalButtons = ($bool == true);
    return $this;
  }
}
?>