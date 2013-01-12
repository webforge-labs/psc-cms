<?php

namespace Psc\CMS\Item;

/**
 * Das Item kann einen Tab mit einem eigenen Label mit dem TabRequest öffnen
 *
 * generisch:
 *
 * JS Bridge:
 * Psc.CMS.TabOpenable
 */
interface TabOpenable {
  
  public function getTabLabel();
  
  public function getTabRequestMeta();
}
?>