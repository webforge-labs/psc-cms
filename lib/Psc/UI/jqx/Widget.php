<?php

namespace Psc\UI\jqx;

use Psc\UI\jQuery;

abstract class Widget extends \Psc\HTML\Base {
  
  protected $widgetName;
  
  protected $autoLoad = TRUE;
  
  protected function doInit() {
    if ($this->autoLoad) {
      jQuery::widget($this->html, $this->widgetName, $this->getWidgetOptions());
    }
  }
  
  abstract public function getWidgetOptions();
  
  
  public function setAutoLoad($bool) {
    $this->autoLoad = $bool;
    return $this;
  }
}
?>