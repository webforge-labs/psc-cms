<?php

namespace Psc\CMS\Item;

interface Buttonable {

  /**
   * Sync Constants with Psc.CMS.Buttonable.js
   */
  const CLICK = 0x000001;
  const DRAG  = 0x000002;
  
  public function getButtonLabel();
  
  public function getFullButtonLabel();
  
  /**
   * Gibt entweder einen Icon namen (ui-icon-$name) oder NULL zurück
   *
   * @return string
   */
  public function getButtonLeftIcon();
  public function getButtonRightIcon();

  /**
   * @return bitmap self::CLICK|self::DRAG
   */
  public function getButtonMode();
}
?>