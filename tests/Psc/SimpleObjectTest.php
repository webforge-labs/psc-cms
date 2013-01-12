<?php

namespace Psc;

use Psc\Data\Type\Type;

/**
 * @group class:Psc\SimpleObject
 */
class SimpleObjectTest extends \Psc\Code\Test\Base {
  
  protected $simpleObject;
  
  public function setUp() {
    $this->chainClass = 'Psc\SimpleObject';
    parent::setUp();
    $this->simpleObject = new ASimpleObjectClass();
  }
  
  public function testInvalidArgumentException() {
    
    $this->assertEquals(
      Exception::invalidArgument(1,
                                 'Psc\ASimpleObjectClass::doSomething()',
                                 'wrongValue',
                                 Type::create('Composite')->setComponents(
                                   Type::create('Integer'),
                                   Type::create('Object<Psc\NiceClass>')
                                 )
                                ),
      
      $this->simpleObject->doSomething('wrongValue')
    );
  }
}

class ASimpleObjectClass extends \Psc\SimpleObject {
  
  public function doSomething($param) {
    
    // eigentlich ja throw, aber so ist der test einfacher
    return $this->invalidArgument(1, $param, array('Integer','Object<NiceClass>'), __FUNCTION__);
  }
  
}
?>