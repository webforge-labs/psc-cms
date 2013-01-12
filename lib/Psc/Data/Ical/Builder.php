<?php

namespace Psc\Data\Ical;

use Webforge\Common\System\File;

class Builder extends \Psc\SimpleObject {
  
  const OVERWRITE = CalendarWriter::OVERWRITE;
  
  /**
   * @var Psc\Data\Ical\CalendarWriter
   */
  protected $calendarWriter;
  
  /**
   * @var Psc\Data\Ical\Calendar
   */
  protected $calendar;
  
  /**
   * Die ics-Datei in die geschrieben wird
   * 
   * @var Webforge\Common\System\File
   */
  protected $file;
  
  public function __construct() {
    
  }
  
  
  public function buildCalendar(File $ics = NULL, CalendarWriter $calendarWriter = NULL) {
    $this->file = $ics;
    $this->calendarWriter = $calendarWriter ?: new CalendarWriter(new Calendar());
    $this->calendar = $this->calendarWriter->getCalendar();
    $this->calendar->setBuilder($this);
    
    return $this;
  }
  
  /**
   * @return Psc\Data\Ical\CalendarEvent
   */
  public function createCalendarEvent($title) {
    return $this->getCalendar()->createEvent($title);
  }
  
  /**
   * @chainable
   */
  public function addCalendarEvent(CalendarEvent $event) {
    $this->getCalendar()->addEvent($event);
    return $this;
  }
  
  /**
   * @return array
   */
  public function getCalendarEvents() {
    return $this->getCalendar()->getEvents();
  }
  
  /**
   * @chainable
   */
  public function write($overwrite = NULL) {
    if (!isset($this->calendarWriter)) throw new BuilderException('Vor write muss buildCalendar() ausgeführt werden');
    
    $this->calendarWriter->write($this->file, $overwrite);
    
    return $this;
  }
  
  /**
   * @return Psc\Data\Ical\Calendar
   */
  public function getCalendar() {
    if (!isset($this->calendar)) throw new BuilderException('Es muss zuerst buildCalendar() ausgeführt werden');
    return $this->calendar;
  }
  
  public function getCalendarWriter() {
    return $this->calendarWriter;
  }
}
?>