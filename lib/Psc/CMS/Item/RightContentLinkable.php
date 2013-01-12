<?php

namespace Psc\CMS\Item;

interface RightContentLinkable extends TabLinkable, Identifyable {

  /**
   * kann auch NULL zurückgeben, wenn das RightContentLinkable nicht mit einem Entity verknüpft ist
   */
  //public function getIdentifier();

  /**
   * kann auch NULL zurückgeben wenn der RightContentLinkable kein Entity ist (statische links, sowas wie "Grid öffnen")
   */
  //public function getEntityName();
}
?>