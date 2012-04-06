<?php

namespace Psc\Code\Event;

use stdClass;

class EventTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Event\Event';
  }
  
  public function testFactory() {
    $this->assertEquals(new Event('Psc.Test', $this), Event::factory('Psc.Test', $this));
  }
  
  public function testIs() {
    $event = new Event('Psc.Test',$this);
    $this->assertTrue($event->is('Psc.Test'));
    $this->assertFalse($event->is('Psc.Blubb'));
  }
  
  public function testConstruct() {
    $event = new Event('onlyName',$this);
    $this->assertEquals('onlyName',$event->getIdentifier());
    $this->assertEquals('onlyName',$event->getName());
    $this->assertEquals(NULL,$event->getNamespace());
    
    $event = new Event('namespace.name',$this);
    $this->assertEquals('namespace.name',$event->getIdentifier());
    $this->assertEquals('name',$event->getName());
    $this->assertEquals('namespace',$event->getNamespace());
    $this->assertEquals($this,$event->getTarget());
    
    $event = new Event('fail.namespace.name',NULL);
    $this->assertEquals('namespace.name',$event->getName());
    $this->assertEquals('fail',$event->getNamespace());
    $this->assertEquals('fail.namespace.name',$event->getIdentifier());
    $this->assertEquals(NULL,$event->getTarget());
  }
  
  public function testSetData() {
    $event = new Event('egal',NULL);
    $event->setData(array('not'=>'allowed')); // wird umgewandelt
    
    $this->assertInstanceOf('stdClass',$event->getData());
  }
  
  public function testSetName() {
    $event = new Event('Psc.Test',$this);
    
    $this->assertEquals('Test',$event->getName());
    $this->assertChainable($event->setName('Frame'));
    
    $this->assertEquals('Frame',$event->getName());
    $this->assertEquals('Psc.Frame',$event->getIdentifier());
  }
  
  public function testSetNamespace() {
    $event = new Event('Psc.Test',$this);

    $this->assertEquals('Test',$event->getName());
    $this->assertEquals('Psc.Test',$event->getIdentifier());
    $this->assertChainable($event->setNamespace('Flitsch'));
    $this->assertEquals('Flitsch.Test',$event->getIdentifier());
  }
  
  public function testSetProcessed() {
    $event = new Event('Psc.Test',$this);
    
    $this->assertFalse($event->isProcessed());
    $this->assertChainable($event->setProcessed(TRUE));
    $this->assertTrue($event->isProcessed());
  }
  
  public function testSetTarget() {
    $e = new Event('Psc.Test', new stdClass());
    $this->assertChainable($e->setTarget($target = new \Psc\DataInput()));
    $this->assertSame($target, $e->getTarget());
  }
  
  public function testToString() {
    $event = new Event('Psc.Test',$this);
    
    $this->assertNotEmpty((string) $event);
  }
}
?>