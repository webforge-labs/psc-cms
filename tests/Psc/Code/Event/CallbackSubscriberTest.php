<?php

namespace Psc\Code\Event;

use Psc\Code\Event\CallbackSubscriber;

/**
 * @group class:Psc\Code\Event\CallbackSubscriber
 */
class CallbackSubscriberTest extends \Psc\Code\Test\Base implements Dispatcher{
  
  protected $manager;
  protected $listenedEvent;
  
  public function setUp() {
    $this->manager = new Manager();
    parent::setUp();
  }

  public function testSubscription() {
    $callback = new CallbackSubscriber($this, 'eventListener');
    $this->assertInstanceOf('Psc\Code\Event\Subscriber',$callback);
    
    $myEventName = 'PscCodeEventCallbackSubscriberTest.Happening';
    
    // wir binden uns an das event
    $this->manager->bind($callback, $myEventName);
    
    // dispatch
    $this->listenedEvent = NULL;
    $expectedEvent = $this->manager->dispatchEvent($myEventName, array(), $this);
    
    // test ob der eventListener das Event ausgegeben hat
    $this->assertNotEmpty($this->listenedEvent);
    $this->assertSame($expectedEvent, $this->listenedEvent);
  }
  
  public function testSubscription_withFilter() {
    /* konflikt:
       soll der filter TRUE oder FALSE zurückgeben um zu filtern?
       
       ich entscheide mich für:
       TRUE wenn das Event getriggered werden soll
       FALSE wenn das Event verworfen werden soll
       
       Eselsbrücke: FALSE ist was negatives und unterbricht damit den flow
    */
    $callback = new CallbackSubscriber($this, 'eventListener');
    $callback->setEventFilter(function ($event) {
      return $event->getData()->filterReturn;
    });
    $myEventName = 'PscCodeEventCallbackSubscriberTest.Happening';
  
    // wir binden uns an das event
    $this->manager->bind($callback, $myEventName);
    
    // test mit filterValue, sodass das event nicht weitergegeben wird mit call()
    $this->listenedEvent = NULL;
    $expectedEvent = $this->manager->dispatchEvent($myEventName, array('filterReturn'=>FALSE), $this);
    $this->assertEmpty($this->listenedEvent,'Event ist gesetzt worden');

    // test mit filterValue die nicht gefiltert wird
    $this->listenedEvent = NULL;
    $expectedEvent = $this->manager->dispatchEvent($myEventName, array('filterReturn'=>TRUE), $this);
    $this->assertSame($expectedEvent, $this->listenedEvent);
    
    // dispatch mit filter zurückgesetzt
    $callback->setEventFilter(NULL);
    $expectedEvent = $this->manager->dispatchEvent($myEventName, array('filterReturn'=>FALSE), $this);
    $this->assertSame($expectedEvent, $this->listenedEvent);
  }
  
  public function testSubscription_withArgumentsMapper() {
    $callback = $this->getMock('Psc\Code\Event\CallbackSubscriber', array('call'), array($this, 'eventListener'));
    
    $callback->setArgumentsMapper(function ($event) {
      return array($event->getData()->p1, $event->getData()->p2);
    });
    
    $myEventName = 'PscCodeEventCallbackSubscriberTest.Happening';
    $this->manager->bind($callback, $myEventName);
    
    $callback->expects($this->once())->method('call')
             ->with($this->equalTo(array('p1value','p2value')));
    
    $this->manager->dispatchEvent($myEventName, array('p1'=>'p1value',
                                                      'p2'=>'p2value'
                                                      ), $this);
  }
  
  public function onCalledWithArguments() {
  }
  
  public function eventListener(Event $event) {
    $this->listenedEvent = $event;
  }
  
  public function getManager() {
    return $this->manager;
  }

}
?>