<?php

namespace Psc\Code\Event;

class ManagerTest extends \Psc\Code\Test\Base {
  
  
  public function testDispatch() {
    $event = new Event('banane',$this);
    
    $manager = new Manager();
    $this->assertEquals(array(),$manager->getSubscribers($event));
    
    
    // nix passiert
    $manager->dispatch($event);
    $subscriber = $this->getMock('Psc\Code\Event\SubscriberMock',array('trigger'));
 
    // Set up the expectation for the update() method
    // to be called only once and with the string 'something'
    // as its parameter.
    $subscriber->expects($this->once())
               ->method('trigger')
               ->with($this->equalTo($event));
    
    /* bind unseren Subscriber an ddas Event */             
    $manager->bind($subscriber,'banane');
    
    $this->assertEquals(1,count($manager->getSubscribers($event)));
    
    /* soll dingsen */
    $manager->dispatch($event);
  }
}

?>