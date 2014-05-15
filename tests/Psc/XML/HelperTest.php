<?php

namespace Psc\XML;

use Psc\XML\Helper;

class HelperTest extends \Psc\Code\Test\Base {

  public function testUTF8DocumentSnipped() {
    $dom = Helper::docPart('<span>Game Templates für Testprodukt</span>');
    
    $q = Helper::query($dom, 'span');
    $this->assertEquals('Game Templates für Testprodukt', $q[0]->nodeValue);
  }
}
