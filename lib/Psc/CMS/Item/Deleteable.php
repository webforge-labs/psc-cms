<?php

namespace Psc\CMS\Item;

interface Deleteable extends Identifyable {
  
  public function getDeleteRequestMeta();

}
?>