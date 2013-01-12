<?php

namespace Psc\UI;

/**
 * 
 * Damit dass wir Button von WidgetBase (ui) ableiten, trennen wir für TabButton Darstellung und Model
 */
class Button extends \Psc\HTML\WidgetBase implements ButtonInterface {
  
  /**
   * @var string
   */
  protected $label;
  
  /**
   * @var string
   */
  protected $leftIcon;
  
  /**
   * @var string
   */
  protected $rightIcon;
  
  /**
   * Der Hint nach dem Button
   * 
   * @var Psc\HTML\Tag
   */
  protected $hint;
  
  /**
   * @var Array
   */
  protected $data;
  
  public function __construct($label, $leftIcon = NULL, $rightIcon = NULL, Array $data = NULL) {
    $this->setLabel($label);
    
    parent::__construct('button'); // construct zuerst dann die icons
    $this->loadWithAttributes();
    
    if (isset($leftIcon))
      $this->setLeftIcon($leftIcon);
    
    if (isset($rightIcon))
      $this->setRightIcon($rightIcon);
    
    if (isset($data))
      $this->setData($data);
  }
  
  public static function create($label, $leftIcon = NULL, $rightIcon = NULL, array $data = NULL) {
    return new static($label, $leftIcon, $rightIcon, $data);
  }
  
  protected function doInit() {
    $this->html = new HTMLTag(
      'button',
      // hier getLabel benutzen, weil TabButton das überschreiben
      new HTMLTag('span', $this->getLabel(), array('class'=>'ui-button-text')),
      array('class'=>'\Psc\button')
    ); 
    
    $this->html->addClass(array('ui-button', 'ui-widget', 'ui-state-default', 'ui-corner-all'));
    
    if (isset($this->leftIcon)) {
      $this->html->addClass('ui-button-text-icon-primary');
    }
    
    if (isset($this->rightIcon)) {
      $this->html->addClass('ui-button-text-icon-secondary');
    }
    
    if (!isset($this->leftIcon) && !isset($this->rightIcon)) {
      $this->html->addClass('ui-button-text-only'); 
    }
    
    parent::doInit();
    
    if (isset($this->data)) {
      foreach ($this->data as $key => $value) {
        jQuery::data($this->html, $key, $value);
      }
    }
    
    if (isset($this->hint))
      $this->html->after($this->hint);
  }
  
  /**
   * http://jqueryui.com/themeroller
   * @param string $icon der Name des Icons ohne ui-icon- davor
   */
  public function setLeftIcon($icon) {
    $this->leftIcon = $icon;
    $this->widgetOptions['icons']['primary'] = 'ui-icon-'.$this->leftIcon;
    return $this;
  }
  
  /**
   * http://jqueryui.com/themeroller
   * @param string $icon der Name des Icons ohne ui-icon- davor
   */
  public function setRightIcon($icon) {
    $this->rightIcon = $icon;
    $this->widgetOptions['icons']['secondary'] = 'ui-icon-'.$this->rightIcon;
    return $this;
  }
  
  public function getLeftIcon() {
    return $this->leftIcon;
  }
  
  public function getRightIcon() {
    return $this->rightIcon;
  }
  
  public function disable($reason = NULL) {
    $this->setHint($reason);
    
    $this->widgetOptions['disabled'] = TRUE;
    return $this;
  }
  
  public function enable() {
    // @TODO hint wieder weg?
    $this->widgetOptions['disabled'] = FALSE;
    return $this;
  }
  
  public function setHint($message) {
    $this->hint = Form::hint($message)->templatePrepend('<br />');
    if ($this->init) {
      $this->html->after($this->hint);
    }
    return $this;
  }
  
  public function getHTML() {
    $this->init();
    return $this->html;
  }
  
  /**
   * @param string $label
   */
  public function setLabel($label) {
    if ($this->init) {
      $this->html->content = $label;
    }
    
    $this->label = $label;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }
  
  /**
   * Setzt die Daten des Buttons
   *
   * jeder schlüssel ruft einen .data($key, $value) Javascript Aufruf auf, wenn der button erstellt wird
   * @param array $data
   */
  public function setData(Array $data) {
    $this->data = $data;
    return $this;
  }
  
  /**
   * Fügt für einen Schlüssel die jQuery Data dem Button hinzu
   *
   * $button->addData('something', 'myvalue');
   *
   * // javascript
   * $('button').data('something') === 'myvalue'
   */
  public function addData($key, $value) {
    if (!isset($this->data)) $this->data = array();
    $this->data[$key] = $value;
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function getData() {
    return $this->data;
  }
}
?>