<?php

namespace Psc\UI;

class OptionsObject extends \Psc\OptionsObject {
  
  public function __construct($options = array()) {
    $this->setDefaultOptions($options);
    
    $this->setDefaultOptions(array(
      'css.namespace'=>\Psc\UI\UI::$namespace,
    ));
  }
}