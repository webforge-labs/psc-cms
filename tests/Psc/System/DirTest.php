<?php

namespace Psc\System;

class DirTest extends \Psc\Code\Test\Base {
  
  public function testDirIsFromWebforgeCommon() {
    $this->assertInstanceOf('Webforge\Common\System\DIr', new \Psc\System\Dir());
  }

}
?>