<?php

namespace Psc\Data\Ical;

use Psc\Data\Ical\CalendarEvent;
use Psc\DateTime\Date;
use Psc\DateTime\DateTime;
use Psc\DateTime\DateInterval;

class CalendarEventTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Data\Ical\CalendarEvent';
    $this->test->object = $this->event = $this->createCalendarEvent('MyTitle');
    parent::setUp();
  }

  public function testConstruct_getTitle() {
    $this->assertInstanceOf('Psc\Data\Ical\CalendarEvent', $this->event);
    $this->assertEquals('MyTitle',$this->event->getTitle());
  }
  
  public function testSetTitle() {
    $this->test->setter('title', $this->getType('String'));
  }
  
  public function testSetDate() {
    $this->test->setter('date', NULL, new Date('21.11.1984'));
  }
  
  public function testSetAllDay() {
    $this->test->setter('allDay',NULL, TRUE);
    $this->test->setter('allDay',NULL, FALSE);
  }
  
  public function testDefaultGetAllDay() {
    $this->assertFalse($this->event->getAllDay());
  }
  
  /**
   * @depends testDefaultGetAllDay
   */
  public function testSetDateSetsAllDayAttribute() {
    $this->event->setDate($dt = new Date('21.11.1984'));
    $this->assertTrue($this->event->getAllDay());
  }

  public function testSetRecurrence() {
    $this->test->setter('recurrence', NULL, new DateInterval('1 YEAR'));
  }
  
  
  public function testIcal() {
    $event = $this->createCalendarEvent('Geburtstag2');
    $event->setDate(new Date('08.12.2012'));
    $event->setRecurrence(new DateInterval('1 YEAR'));
    $event->setCreated(new DateTime('04.02.2012 10:21:08'));
    $event->setStamp(new DateTime('04.02.2012 10:21:32'));
    $event->setModified(new DateTime('04.02.2012 10:21:09'));
    $event->setUID('ho457t8vphmuud2qjmfaej73l8@ps-webforge.com');
    
    $this->assertEquals($this->getFile('vevent.ics')->getContents(),
                        $event->ical()
                        );
  }
  
  public function testGetBuilder() {
    $event = new CalendarEvent($cal = new Calendar(),'test');
    $cal->setBuilder($this->createBuilder());
    $this->assertInstanceOf('Psc\Data\Ical\Builder',$event->getBuilder());
  }

  public function createCalendarEvent($title) {
    return new CalendarEvent($this->createCalendar(), $title);
  }

  public function createCalendarEventWithBuilder($title) {
    return $this->createCalender()->createEvent($title);
  }
  
  public function createCalendar() {
    return $this->createBuilder()->buildCalendar($this->doublesManager->createFileMock())->getCalendar();
  }
  
  public function createBuilder() {
    return new Builder();
  }
}
?>