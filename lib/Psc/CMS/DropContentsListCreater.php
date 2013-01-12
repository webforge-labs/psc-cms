<?php

namespace Psc\CMS;

interface DropContentsListCreater {

  /**
   * @return Psc\CMS\DropContentsList
   */
  public function newDropContentsList($label);


}
?>