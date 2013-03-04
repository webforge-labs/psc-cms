<?php

namespace Psc\UI;

use Webforge\Common\ArrayUtil AS A;
use stdClass;
use Psc\CMS\TabsContentItem;
use Psc\JS\Helper as JSHelper;
use Psc\JS\Lambda;
use Psc\Code\Code;
use Psc\CMS\AjaxMeta;

/**
 * Eine Combo aus einer Combo-Box (zum selecten der $items)
 * und einer DropBox (zum pflegen der $assignedItems)
 *
 * diese Kombo kann benutzt werden um 1:n beziehungen für ein Objekt zu pflegen
 *
 * init() wird notfalls bei html() aufgerufen. Wird itemInfo nicht gesetzt versucht die klasse sich dies aus den items und assignedItems zu erschließen
 */
class FormComboDropBox extends \Psc\Object {
  
  public static $cacheComboBox;
  
  protected $label;
  protected $name;
  protected $id;
  
  protected $comboBox;
  protected $dropBox;
  protected $content;

  protected $init = FALSE;
  
  /**
   * @var stdClass
   */
  protected $itemInfo;
  
  /**
   * Die Items, die in der ComboBox zu selecten sind (avaible)
   *
   * @var TabsContentItem[]
   */
  protected $items;
  
  /**
   * Die Items, die bereits zugeordnet waren
   * 
   * @var TabsContentItem[]
   */
  protected $assignedItems;
  
  
  /**
   * @var Psc\CMS\AjaxMeta
   */
  protected $ajaxMeta;
  
  public function __construct($label, $name, $items, $assignedItems, $id = NULL) {
    $this->label = $label;
    $this->name = $name;
    
    $this->items = $items;
    $this->assignedItems = $assignedItems;
    $this->id = $id;
    
    $this->itemInfo = new stdClass;
    
    $this->dropBox = Form::dropBox(NULL, $this->name, $this->assignedItems);
    $this->comboBox = new FormComboBox(NULL, // label
                                       array_merge(array('disabled'), (array) $this->name, array('comboBox')), // name
                                       $items,
                                       NULL // kein aktuell ausgewähltes Item (könnte vll das erste aus der Liste sein oder so
                                      );
  }
  
  public function init() {
    // die init Funktion gibt es, damit man vorher z.b. noch itemInfo von hand aus dem Formular setzen kann
    
    if (!$this->init) {
      // hier ist die combobox noch nicht fertig, sondern nur ein select und wird hinterher durchs widget die comboBox
      //
      //$options = A::implode($this->items, "\n  ", function ($item) {
      //  return '<option value="'.HTML::esc(json_encode($this->exportItem($item),JSON_FORCE_OBJECT)).'">'.HTML::esc($item->getFormLabel()).'</option>';
      //});
      $comboOptions = $this->comboBox->getWidgetOptions();
      $dropOptions = $this->dropBox->getWidgetOptions();
      
      $ii = $this->getItemInfo();
      $comboOptions->itemType = $ii->type;
      $dropOptions->itemType = $ii->type;
      $comboOptions->itemData = isset($ii->data) ? $ii->data : array();
      $comboOptions->ajaxData['ac.length'] = 200;
      if (!isset($comboOptions->width)) $comboOptions->width = '80%';
      
      /* Connect */
      $comboOptions->selected = new Lambda("function (event, ui) {
                                              var box = $(this).nextAll('div.psc-cms-ui-drop-box');
                                              
                                              $.pscUI('form','addItemToBox', box, ui.item);
                                           }");
      $dropOptions->exists = new Lambda("function (event, ui) {
                                          alert('Dieser Eintrag wurde bereits hinzugefügt');
                                        }");
      
      $this->comboBox->init();
  
      $this->content = new stdClass();
      $this->content->comboBox = $this->comboBox;
      $this->content->dropBox = $this->dropBox;

      $this->init = TRUE;
    }
    return $this;
  }
  
  protected function exportItem(TabsContentItem $item) {
    list($type,$identifier) = $item->getTabsId();
        
    $jsItem = array(
      'type'=>$type,
      'identifier'=>$identifier,
      'data'=>$item->getTabsData()
    );
    
    return $jsItem;
  }

  public function getItemInfo() {
    if (!isset($this->itemInfo) || !isset($this->itemInfo->type)) {
      /* wir versuchen ein bißche Magie um die API im Formular sehr einfach halten zu können
        dabei müssen wir natürlich ein paar Annahmen machen
        
        wenn items leer ist, haben wir hier sowieso einen komischen fall.
        die combobox macht ja nur dann sinn, wenn es auch 1:n Items zuzordnen gibt. Also müssen wir eigentlich nichts
        machen, wenn es keine Items gibt?
      */
      
      if (count($this->items) > 0) {
        $sampleItem = A::first(Code::castArray($this->items));
      
      } elseif (count($this->assignedItems) > 0) {
        // das ist wie gesagt ein komischer fall. wie kann ein item noch assigned sein, wenn es nicht in $items ist ?
        $samplteItem = A::first(Code::castArray($this->assignedItems));
      
      } else {
        /* dies ist der fall, wenn es keine items und keine assigned items gibt,
          kann halt eine leere Box sein, die mit Ajax-Items geladen wird
          dann müssen wir aber den Typ von außen setzen
          dies geht aber nur wenn das hier erstmal durchläuft
        */
        //throw new FormDesignException('Es konnte der Typ der FormComboDropBox nicht bestimmt werden, da es weder items noch assignedItems gab',17839);
      }
      
      if (isset($sampleItem)) {
        if (!($sampleItem instanceof \Psc\CMS\TabsContentItem)) {
          throw new FormDesignException('In einer FormComboDropBox können noch keine anderen Elemente als ContentTabsItem s vorkommen. '.Code::varInfo($sampleItem));
        }
        
        list ($type,$id) = $sampleItem->getTabsId();
        $this->itemInfo->type = $type;
        $this->itemInfo->data = new stdClass();
      }
    }
    
    return $this->itemInfo;
  }

  
  public function html() {
    $this->init();
    
    return Form::Group($this->label,$this->content)->addClass('\Psc\combo-drop-box-wrapper');
  }
  
  public function setMultiple($bool) {
    $this->dropBox->setMultiple($bool);
    return $this;
  }
  
  public function getMultiple() {
    return $this->dropBox->getMultiple();
  }
  
  public function setAjaxMeta(AjaxMeta $ajaxMeta) {
    $this->ajaxMeta = $ajaxMeta;
    $this->comboBox->setAjax(TRUE);
    $this->comboBox->getWidgetOptions()->ajaxUrl = $ajaxMeta->getURL();
    $this->comboBox->getWidgetOptions()->ajaxMethod = $ajaxMeta->getMethod();
    return $this;
  }
  
  public function disableAjax() {
    $this->ajaxMeta = NULL;
    $this->comboBox->setAjax(FALSE);
    return $this;
  }
  
  public function __toString() {
    return (string) $this->html();
  }
}