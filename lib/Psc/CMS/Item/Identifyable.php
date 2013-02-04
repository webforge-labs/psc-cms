<?php

namespace Psc\CMS\Item;

interface Identifyable {
  
  /**
   * @param mixed scalar
   */
  public function getIdentifier();

  /**
   * Short Name
   */
  public function getEntityName();
}
?>