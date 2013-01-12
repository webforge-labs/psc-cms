<?php

namespace Psc\UI;

class FormInputSet extends \Psc\OptionsObject {
  
  protected $inputs;  // inputs => morph() => elements
  protected $elements;
  
  protected $flags;
  
  protected $width;
  
  protected $styles;
  
  protected $morphed = FALSE;
  
  public function __construct(Array $inputs = array()) {
    $this->inputs = $inputs;
    $this->elements = array();
    $this->styles = array();
  }
  
  public function morph() {
    foreach ($this->inputs as $key=>$input) {
      if (!($input instanceof FormInput)) {
        $input = new FormInput($input);
      }
      
      foreach ($this->styles as $style =>$v) {
        if ($input->isAllowedStyle($style))
          $input->getTag()->setStyle($style,$v);
      }
      
      
      if ($this->getOption('wrap',TRUE)) {
        $wrapper = HTML::tag('div', new \stdClass,array('class'=>'input-set-wrapper'));
        $wrapper->content->input = $input;
        
        $wrapper->content->input->morph();
        
        $this->elements[$key] = $wrapper;
      } else {
        $input->morph();
        
        $this->elements[$key] = $input;
      }
    }
    
    $this->morphed = TRUE;
    return $this;
  }
  
  public function isMorphed() {
    return $this->morphed;
  }
  
  public function setWidth($w) {
    $this->setStyle('width',$w);
    return $this;
  }

  /**
   * Setzt einen HTML-Style für alle Elemente
   */
  public function setStyle($style,$value) {
    if ($this->morphed) {
      throw new Exception('setStyle() vor dem morphen aufrufen!');
    }
    $this->styles[$style] = $value;
    return $this;
  }
  
  /**
   * Wenn noch nicht gemorphed, wird das getan
   */
  public function html() {
    if (!$this->isMorphed())
      $this->morph();
      
    $html = NULL;
    foreach ($this->elements as $el) {
      $html .= $el->html()."\n";
    }
      
    return $html;
  }
  
  public function __toString() {
    return $this->html();
  }
}


?>