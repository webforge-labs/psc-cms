<?php

namespace Psc\CMS\Item;

interface EditButtonable extends TabOpenable, Buttonable {
  
  public function getFormRequestMeta(); // ist eigentlich ein Alias für getTabRequestMeta
}
?>