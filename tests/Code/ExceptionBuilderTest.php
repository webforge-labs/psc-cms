<?php

namespace Psc\Code;

use Psc\Code\ExceptionBuilder;

/**
 * @group class:Psc\Code\ExceptionBuilder
 */
class ExceptionBuilderTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Code\ExceptionBuilder';
    parent::setUp();
  }

  public function testConstruct() {
    $builder = $this->createExceptionBuilder('Psc\Code\MySpecialException','Ich bin eine spezielle Exception');
    
    $this->assertChainable($builder->setCode(200));
    $this->assertChainable($builder->setPrevious($previous = new \Psc\Exception('Ich bin previous')));
    $this->assertChainable($builder->set('info', array('nothing','interesting','here')));
    
    $this->assertInstanceOf('Psc\Code\MySpecialException', $ex = $builder->end());
    $this->assertSame($previous,$ex->getPrevious());
    $this->assertEquals(200,$ex->getCode());
    $this->assertEquals('Ich bin eine spezielle Exception', $ex->getMessage());
    $this->assertEquals($ex->info, array('nothing','interesting','here'));
  }
  
  public function testSprintfConstruct() {
    $builder = new ExceptionBuilder('Psc\Code\MySpecialException','I: %d, am: "%s" formatted', array(7,'nice'));
    
    $this->assertInstanceOf('Psc\Code\MySpecialException', $ex = $builder->end());
    $this->assertEquals('I: 7, am: "nice" formatted', $ex->getMessage());
  }

  public function createExceptionBuilder($classFQN, $msg) {
    return new ExceptionBuilder($classFQN, $msg);
  }
}

class MySpecialException extends \Psc\Exception {
  
  public $info;
  
}
?>