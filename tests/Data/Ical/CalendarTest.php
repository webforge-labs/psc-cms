<?php

namespace Psc\Data\Ical;

use Psc\Data\Ical\Calendar;

class CalendarTest extends \Psc\Code\Test\Base {
  
  protected $eventClass;
  protected $builderClass;
  
  protected $calendar;

  public function setUp() {
    $this->chainClass = 'Psc\Data\Ical\Calendar';
    $this->eventClass = 'Psc\Data\Ical\CalendarEvent';
    $this->builderClass = 'Psc\Data\Ical\Builder';
    $this->calendar = $this->createCalendar();
    parent::setUp();
  }

  public function testConstruct() {
    $this->assertChainable($this->calendar);
  }

  public function testCreateEvent() {
    $this->assertInstanceOf($this->eventClass, $event = $this->calendar->createEvent('mytitle'));
    $this->assertEquals('mytitle',$event->getTitle());
    return $event;
  }
  
  public function testCreateEvent_SetsBuilder() {
    $this->calendar->setBuilder($this->createBuilder());
    
    $this->assertInstanceOf($this->builderClass, $this->calendar->getBuilder(),'PreCondition');
    $event = $this->calendar->createEvent('mytitle');
    $this->assertInstanceOf($this->builderClass, $event->getBuilder());
  }
  
  public function testAddEvent() {
    $event = new CalendarEvent($this->calendar, 'mytitle');
    $this->calendar->addEvent($event);
    
    $this->assertEquals(array($event), $this->calendar->getEvents());
  }
  
  
  public function testGetEvents() {
    $this->assertInternalType('array',$this->calendar->getEvents());
    $this->calendar->createEvent('mytitle');
    $this->assertCount(1,$this->calendar->getEvents());
  }

  public function createCalendar() {
    return new Calendar();
  }
  
  public function createBuilder() {
    $builder = new Builder();
    return $builder->buildCalendar($this->doublesManager->createFileMock());
  }
}
?>