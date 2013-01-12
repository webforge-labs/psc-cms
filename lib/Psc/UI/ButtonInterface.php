<?php

namespace Psc\UI;

interface ButtonInterface extends \Psc\HTML\HTMLInterface {
  
  public function getHTML();
  
  public function setLabel($label);
  
  public function getLabel();
  
  /**
   * http://jqueryui.com/themeroller/
   * @param string $icon der Name des Icons ohne ui-icon- davor
   */
  public function setRightIcon($icon);
  public function setLeftIcon($icon);
  
  public function getRightIcon();
  public function getLeftIcon();

  public function enable();
  
  public function disable($reason = NULL);
}
?>