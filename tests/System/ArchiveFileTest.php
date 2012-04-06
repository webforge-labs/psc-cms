<?php

namespace Psc\System;

use Psc\System\ArchiveFile;

class ArchiveFileTest extends \Psc\Code\Test\Base {

  public function testAPI() {
    $this->markTestIncomplete('Feature noch nicht geklärt');
    $af = new ArchiveFile('.','filename.txt');
    
    $this->assertEquals('txt', $af->getExtension());
    $this->assertEquals('non', $af->setExtension('non')->getExtension());
    
    
  }
}
?>