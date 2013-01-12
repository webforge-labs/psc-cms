<?php

namespace Psc\HTML;

use Psc\UI\jQuery;
use Psc\JS\JSONConverter;

class WidgetBase extends \Psc\HTML\Base {
  
  // load with..
  const JAVASCRIPT = 'javascript';
  const ATTRIBUTES = 'attributes';
  
  protected $widgetOptions;
  
  protected $widgetName;
  
  protected $loadWith = self::JAVASCRIPT;

  /**
   * @var Psc\JS\JSONConverter
   */
  protected $jsonConverter;
  
  
  public function __construct($widgetName, Array $options = array()) {
    $this->widgetOptions = $options;
    $this->widgetName = $widgetName;
  }
  
  /**
   * Initialisiert das Widget des objektes
   *
   * wenn loadWith === self::JAVASCRIPT ist
   * an das Element wird inline Javascript angehängt welcher jQuery( elementSelector ).$widgetName.({ $options}) aufruft
   *
   * ansonsten wird im Element die attribute
   * data-widget und data-widget-options (ein JSON string) gesetzt
   */
  protected function doInit() {
    if ($this->loadWith === self::JAVASCRIPT) {
      jQuery::widget($this->html, $this->widgetName, (array) $this->widgetOptions);
    } else {
      $this->html
        ->setAttribute('data-widget', $this->widgetName)
        ->setAttribute('data-widget-options', $this->getJSONConverter()->stringify((object) $this->widgetOptions))
        ->addClass('widget-not-initialized')
      ;
    }
  }
  
  public function getJSONConverter() {
    if (!isset($this->jsonConverter)) {
      $this->jsonConverter = new JSONConverter();
    }
    return $this->jsonConverter;
  }
  
  public function loadWithAttributes() {
    $this->loadWith = self::ATTRIBUTES;
    return $this;
  }
}
?>