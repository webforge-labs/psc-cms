<?php

namespace Psc\UI;

interface TabButtonInterface extends ButtonInterface {
  
  /**
   * @chainable
   */
  public function clickableAndDraggable();

  /**
   * @chainable
   */
  public function onlyDraggable();

  /**
   * @chainable
   */
  public function onlyClickable();
  
  /**
   * @return Psc\CMS\RequestMeta
   */
  public function getTabRequestMeta();
}
?>