<?php

namespace Psc\UI;

class ControllerCallTabsItem extends TabsItem {
  
  protected $ajaxParams;
  
  /**
   * @param array $ajaxParams das sind nicht die daten für das TCI (siehe TCIControllerCallTabsItem)
   */
  public function __construct($controllerName, $todo, $label, Array $ajaxParams= array()) {
    parent::__construct($controllerName, NULL, $label);
    
    $this->todo = 'ctrl';
    $this->ctrlTodo = $todo;
    $this->ajaxParams = $ajaxParams;
  }
  
  protected function getAjaxParams() {
    return array_merge(parent::getAjaxParams(),$this->ajaxParams);
  }
}
?>