<?php

namespace Psc\TPL;

class TabsItem extends \Psc\UI\TabsItem {
  
  protected $tpl;
  protected $vars;
  
  /**
   * @param mixed $tpl nicht das TPL\Template sondern die Referenz für eines
   */
  public function __construct($tpl, $label, Array $vars = array()) {
    $this->tpl = $tpl;
    $id = is_array($tpl) ? implode('.',$tpl) : (string) $tpl;
    $this->vars = $vars;
    
    parent::__construct('tpltabsitem',$id, $label);
    $this->todo = 'tpl';
    $this->ctrlTodo = NULL;
  }
  
  protected function getURLParams() {
    $p = parent::getURLParams();
    $p['tpl'] = $this->tpl;
    $p['vars'] = $this->vars;
    return $p;
  }
  
}

?>