<?php

namespace Psc\UI;

class HTMLTag extends \Psc\HTML\Tag {
  
  protected function setUp() {
    // wird vom constructor aufgerufen
    $this->setDefaultOptions(array(
      'css.namespace'=>UI::$namespace,
    ));
  }
  
  /**
   * Gibt die Attribute des Tags zurück
   *
   * Fügt jeder Klasse des Tags einen namespace hinzu, sofern die klasse mit \Psc anfängt
   */
  public function getAttributes() {
    $attributes = parent::getAttributes();
    /* wir fügen den namespace hinzu wenn es nötig ist */
    if (isset($attributes['class']) && ($namespace = $this->getOption('css.namespace')) != '') {
      $attributes['class'] = array_map(array('\Psc\UI\UI','getClass'), $attributes['class']);
    }
    return $attributes;
  }
}
?>