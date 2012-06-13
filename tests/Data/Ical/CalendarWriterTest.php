<?php

namespace Psc\Data\Ical;

use Psc\Data\Ical\CalendarWriter;

/**
 * Akzeptanztest siehe in BuilderTest
 * @group class:Psc\Data\Ical\CalendarWriter
 */
class CalendarWriterTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Data\Ical\CalendarWriter';
    $this->test->object = $this->writer = $this->createCalendarWriter();
    parent::setUp();
  }
  
  

  public function testConstruct() {
    $this->assertInstanceOf('Psc\Data\Ical\CalendarWriter', $this->writer);
  }
  
  public function testGetCalendar() {
    $this->assertInstanceOf('Psc\Data\Ical\Calendar', $this->writer->getCalendar());
  }

  public function createCalendarWriter() {
    return new CalendarWriter(new Calendar());
  }
}
?>