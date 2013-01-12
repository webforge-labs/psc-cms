<?php

namespace Psc\UI;

/**
 * Ein Element von einem InputSet
 */
class FormInput extends \Psc\OptionsObject {
  
  protected $tag;
  
  protected $hint;
  
  protected $flags = 0x000000;
  
  public function __construct(HTMLTag $inputTag, $hint = NULL, $flags = 0x000000) {
    $this->tag = $inputTag;
    $this->hint = $hint;
    $this->flags = $flags;
  }
  
  public function morph() {
    
    if (($this->flags & Form::OPTIONAL) == Form::OPTIONAL && isset($this->tag->templateContent->label->content)) {
      //@TODO geht hier für komplexere inputs nicht (z. B. combobox) => interface
      $this->tag->templateContent->label->content .= ' (optional)';
    }

    if (isset($this->hint)) {
      $this->tag->templateContent->hint = Form::hint($this->hint);
      
      if ($this->tag->getTag() != 'div')
        $this->tag->templateAppend('<br />');
      
      $this->tag->templateAppend('%hint%');
    }
    
    /* das muss hinter hint in hint machen wir selber immer einen umbruch */
    if (($this->flags & Form::BR) == Form::BR) {
      $this->tag->templateAppend('<br />');
    }
  }
  
  public function isAllowedStyle($style) {
    if ($this->tag->hasStyle($style))
      return FALSE;
    
    if ($style == 'width' && $this->tag->getTag() == 'input' && (
                                                                 $this->tag->getAttribute('type') == 'checkbox'
                                                                 ||
                                                                 $this->tag->getAttribute('type') == 'file'
                                                                )) {
      return FALSE;
    }
    
    if ($style == 'width' && $this->tag->getTag() == 'div' && $this->tag->hasClass('radios-wrapper')) {
      return FALSE;
    }
    
    return TRUE;
  }
  
  public function addFlag($flag) {
    $this->flags |= $flag;
  }
  
  public function removeFlag($flag) {
    $this->flags = $this->flags & ~$flag;
  }
  
  public function html() {
    return (string) $this->tag;
  }
  
  public function __toString() {
    return (string) $this->tag;
  }
}


?>