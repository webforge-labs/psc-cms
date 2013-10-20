<?php

namespace Psc\TPL\AST;

use Psc\TPL\AST\Link;

/**
 * @group class:Psc\TPL\AST\Link
 */
class LinkTest extends \Psc\Code\Test\Base {

  public function testInterface() {
    $this->assertInstanceOf('Webforge\Types\Interfaces\Link',new Link('ftp://geheim.de'));
  }
}
