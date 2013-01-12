<?php

namespace Psc\ICTS;

use \Psc\Preg;

class HTMLTemplate extends StandardTemplate {
  
  protected $tplName;
  protected $tpl;
  
  /**
   * @var array
   */
  protected $compact = array();
  
  public function __construct(HTMLTemplate $parentTemplate) {
    if (!isset($this->name))
      $this->name = Preg::qmatch(mb_strtolower($this->getClassName()),'/^(.*)template$/',1);
      
    
    parent::__construct($parentTemplate);

    if (!isset($this->tplName)) {
      $this->tplName = $this->name;
    }
  }

  public function get() {
    $this->init();
    $this->tpl = new \Psc\TPL\Template($this->tplName);
    
    /* gibt alle Vars ans Template weiter */
    $this->tpl->setVars($this->compact());
    
    return $this->tpl->get();
  }
  
  public function init() {
    
  }
  
  public function compact() {
    $vars = (array) $this->binder->g(array(), array()); // g ist hier dann mit prefix natürlich
    foreach ($this->compact as $prop) {
      $vars[$prop] = $this->$prop;
    }
    return $vars;
  }
  
  public function createTemplate($name) {
    $this->$name = new HTMLTemplate($this,$name);
    
    return $this->$name;
  }
}

?>