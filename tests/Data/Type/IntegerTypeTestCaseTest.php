<?php

namespace Psc\Data\Type;

use Psc\Data\Type\IntegerTypeTestCase;

/**
 * @group class:Psc\Data\Type\IntegerTypeTestCase
 */
class IntegerTypeTestCaseTest extends \Psc\Code\Test\Base {

  public function testStubIncomplete() {
    $this->assertInstanceOf('Psc\Code\Test\Base', new IntegerTypeTestCase());
  }
}
?>