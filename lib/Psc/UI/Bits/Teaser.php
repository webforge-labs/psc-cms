<?php

namespace Psc\UI\Bits;

interface Teaser {
  
  /**
   * @return Psc\UI\Bits\Heading
   */
  public function getHeading();

  /**
   * @return Psc\UI\Bits\TextContent
   */
  public function getContent();
  
  /**
   * @return Psc\UI\Bits\Link|NULL
   */
  public function getLink();
}
?>