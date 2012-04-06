<?php

namespace Psc\Data\Type;

use Psc\Data\Type\Type;

class TypeTest extends \Psc\Code\Test\Base {

  public function testAPI() {
    $type1 = new CustomStringType();      
    $this->assertEquals('CustomString',$type1->getName());
    $this->assertInstanceOf('Psc\Data\Type\CustomStringType', $type1);
    
    $type2 = Type::create('CustomString');
    $this->assertInstanceOf('Psc\Data\Type\CustomStringType', $type2);
    $this->assertEquals('CustomString',$type2->getName());

    $this->assertNotSame($type1,$type2);
    
    $type3 = CustomStringType::create();
    $this->assertInstanceOf('Psc\Data\Type\CustomStringType', $type3);
    $this->assertEquals('CustomString',$type3->getName());
    
    
    $this->assertException('Psc\Data\Type\Exception', function () {
      $type4 = \Psc\Data\Type\Type::create();
    });
  }
}

class CustomStringType extends Type {
  
}
?>