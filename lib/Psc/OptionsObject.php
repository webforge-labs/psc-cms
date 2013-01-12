<?php

namespace Psc;

class OptionsObject extends \Psc\Object {
  
  protected $options = array();

  public function setOption($name,$value = TRUE) {
    $this->options[$name] = $value;
    return $this;
  }
  
  public function setDefaultOptions(Array $options) {
    foreach ($options as $name => $value) {
      if (!isset($this->options[$name])) {
        $this->options[$name] = $value;
      }
    }
    return $this;
  }
  
  /**
   *
   * wenn die option voher auf NULL gesetzt wurde, wird auch $default zurückgegeben
   */
  public function getOption($name, $default = NULL) {
    return isset($this->options[$name]) ? $this->options[$name] : $default;
  }
}

?>