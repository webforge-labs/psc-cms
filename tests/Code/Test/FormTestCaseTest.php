<?php

namespace Psc\Code\Test;

use Psc\Code\Test\FormTestCase;

class FormTestCaseTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->object = $this->getMockForAbstractClass('Psc\Code\Test\FormTestCase');
  }
  
  public function testInstance() {
    $this->assertInstanceOf('Psc\Doctrine\DatabaseTest', $this->object);
  }
}
?>