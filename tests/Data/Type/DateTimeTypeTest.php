<?php

namespace Psc\Data\Type;

use Psc\Data\Type\DateTimeType;

class DateTimeTypeTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\DateTimeType';
    parent::setUp();
  }

  public function testConstruct() {
    $dtt = Type::create('DateTime');
    $this->assertInstanceOf('Psc\Data\Type\ObjectType',$dtt);
    $this->assertEquals('\Psc\DateTime\DateTime',$dtt->getPHPHint());
    $this->assertEquals('Psc\DateTime\DateTime',$dtt->getClassFQN());
  }

  public function createDateTimeType() {
    return new DateTimeType();
  }
}
?>