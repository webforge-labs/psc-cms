<?php

namespace Psc\Data\Ical;

use Psc\Data\Ical\Builder;
use Psc\DateTime\Date;
use Psc\DateTime\DateTime;
use Psc\DateTime\DateInterval;

class BuilderTest extends \Psc\Code\Test\Base {
  
  protected $calendarClass;
  protected $eventClass;
  
  // mock
  protected $calendarWriter;
  protected $file;
  
  // cut
  protected $build;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Ical\Builder';
    $this->eventClass = 'Psc\Data\Ical\CalendarEvent';
    $this->calendarClass = 'Psc\Data\Ical\Calendar';
    parent::setUp();
    
    $this->calendar = new Calendar();
    $this->calendarWriter = $this->getMock('Psc\Data\Ical\CalendarWriter', array('write'), array($this->calendar ));
    
    $this->builder = $this->createBuilder()->buildCalendar($this->file = $this->doublesManager->createFileMock(),
                                                           $this->calendarWriter
                                                           );
  }

  public function testBuildCalendar() {
    $this->assertChainable($this->createBuilder()->buildCalendar($this->doublesManager->createFileMock()));
  }
  
  public function testBuildCalendar_setsBuilderInCalender() {
    $this->assertInstanceOf($this->calendarClass,$calendar = $this->builder->getCalendar());
    $this->assertChainable($calendar->getBuilder(),'builder ist im Calendar von buildCalendar() nicht gesetzt worden');
  }
    
  public function testBuildCalendar_setsBuilderInCalenderDPI() {
    // testet ohne die dependencyInjection in Setup
    $this->assertInstanceOf($this->calendarClass,$calendar = $this->createBuilder()->buildCalendar($this->file)->getCalendar());
    $this->assertChainable($calendar->getBuilder(),'builder ist im Calendar von buildCalendar() nicht gesetzt worden. (ohne DPI)');
  }
  
  /**
   * @expectedException Psc\Data\Ical\BuilderException
   */
  public function testBuildCalendar_hasToBeCalledBeforeCalendarEvent() {
    $this->createBuilder()->createCalendarEvent('errorneous');
  }

  /**
   * @expectedException Psc\Data\Ical\BuilderException
   */
  public function testGetBuilder_ThrowsIfBuildIsNotCalledBefore() {
    $this->createBuilder()->getCalendar();
  }

  public function testCreateCalendarEvent() {
    $this->assertInstanceOf($this->eventClass, $event = $this->builder->createCalendarEvent('mytitle'));
    $this->assertEquals('mytitle',$event->getTitle());
    return $event;
  }
  
  public function testAddCalendarEvent() {
    $event = new CalendarEvent($this->builder->getCalendar(), 'mytitle');
    $this->builder->addCalendarEvent($event);
    
    $this->assertEquals(array($event), $this->builder->getCalendarEvents());
  }
  
  public function testGetCalendarEvents() {
    $this->assertInternalType('array',$this->builder->getCalendarEvents());
    $this->builder->createCalendarEvent('mytitle');
    $this->assertCount(1,$this->builder->getCalendarEvents());
  }
  
  /**
   * @depends testCreateCalendarEvent
   * @depends testBuildCalendar_setsBuilderInCalender
   */
  public function testGetBuilderReturning($event) {
    $this->assertChainable($event->getBuilder(),'Event gibt keinen Builder zurück!');
  }
  
  public function testWrite_callsWithOverwrite() {
    $this->calendarWriter->expects($this->once())
      ->method('write')
      ->with($this->equalTo($this->file), $this->equalTo('overwriteparam'))
    ;
    $this->assertSame($this->calendarWriter, $this->builder->getCalendarWriter());
    $this->assertChainable($this->builder->write('overwriteparam'));
  }
  
  /**
   * @depends testGetBuilderReturning
   * @depends testBuildCalendar
   */
  public function testAcceptance() {
    $builder = new Builder();
    $builder->buildCalendar($this->newFile('test.ics','test'))
      ->createCalendarEvent('Geburtstag1')
        ->setDate(new Date('05.02.2012'))
        ->setUID('4f2d7f68b1184@ps-webforge.com')
        ->setRecurrence(new DateInterval('1 YEAR'))
        ->getBuilder()
      
      ->createCalendarEvent('Geburtstag2')
        ->setDate(new Date('08.12.2012'))
        ->setUID('4f2d7f68b17e6@ps-webforge.com')
        ->setRecurrence(new DateInterval('1 YEAR'))
        ->getBuilder()

      ->createCalendarEvent('Philipp Scheit')
        ->setDate(new Date('21.11.1984'))
        ->setRecurrence(new DateInterval('1 YEAR'))
        ->setUID('4f2d7f68b0a98@ps-webforge.com')
        ->getBuilder()
        
      ->write(Builder::OVERWRITE)
      ;
  }

  public function createBuilder() {
    return new Builder();
  }
}
?>