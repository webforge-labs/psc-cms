<?php

namespace Psc\HTML;

interface HTMLInterface {
  
  /**
   * @return string|Tag irgendetwas was mit __toString in einen String umgewandelt werden kann der HTML repräsentiert
   */
  public function html();
}
?>