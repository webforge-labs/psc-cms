<?php

namespace Psc\Code;

interface Info {
  
  /**
   * @return ein Einzeiler der in Exception-Messages passt und das Objekt möglichst genau beschreibt
   */
  public function getVarInfo();
}
?>