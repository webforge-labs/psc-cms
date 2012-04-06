<?php

namespace Psc\TPL\AST;

use Psc\TPL\AST\Link;

class LinkTest extends \Psc\Code\Test\Base {

  public function testInterface() {
    $this->assertInstanceOf('Psc\Data\Type\Interfaces\Link',new Link('ftp://geheim.de'));
  }
}
?>