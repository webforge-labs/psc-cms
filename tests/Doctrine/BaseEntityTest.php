<?php

namespace Psc\Doctrine;

use Psc\Doctrine\BaseEntity;

class BaseEntityTest extends \Psc\Code\Test\Base {

  public function testToString_isExceptionSafe() {
    $base = $this->getMock('Psc\Doctrine\BaseEntity', array('getEntityLabel'));
    $base->expects($this->once())->method('getEntityLabel')
         ->will($this->throwException(new \Psc\Code\NotImplementedException('an error')));
    
    $this->expectOutputRegex('/an error/');
    $this->assertEmpty((string) $base);
  }
  
  public function testCallSetter() {
    $base = $this->getMock(__NAMESPACE__.'\MyBaseEntity', array('setVar'));
    $base->expects($this->once())->method('setVar')->with($this->equalTo('param'));
    
    $base->callSetter('var','param');
  }

  public function testCallGetter() {
    $base = $this->getMock(__NAMESPACE__.'\MyBaseEntity', array('getVar'));
    $base->expects($this->once())->method('getVar');
    
    $base->callGetter('var');
  }
}

class MyBaseEntity extends BaseEntity {
  protected $var;
  
  public function setVar() {
  }
  
  public function getVar() {
  }
}
?>