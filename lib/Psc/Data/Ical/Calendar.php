<?php

namespace Psc\Data\Ical;

use Psc\PSC;

class Calendar extends \Psc\Data\Ical\Base {
  
  /**
   * @var array
   */
  protected $events = array();
  
  /**
   * @var Psc\Data\Ical\Builder
   */
  protected $builder;
  
  
  protected $calScale = 'GREGORIAN';
  
  public function __construct() {
    
  }

  public function ical() {
    $ical  = $this->contentLine('BEGIN','VCALENDAR');
    $ical .= $this->contentLine('PRODID',sprintf('-//ps-webforge//Psc-CMS %s//EN', PSC::getVersion()));
    $ical .= $this->contentLine('VERSION','2.0');
    $ical .= $this->contentLine('CALSCALE',$this->getCalScale());
    $ical .= $this->contentLine('METHOD','PUBLISH');
    //$ical .= X-WR-CALNAME:test // http://groups.google.com/group/microformats/msg/38f97bf3caa4559e?pli=1
    $ical .= $this->contentLine('X-WR-TIMEZONE',date_default_timezone_get());
    $ical .= $this->contentLine('X-WR-CALDESC',NULL);
    
    $ical .= $this->icalTimezone();
    
    foreach ($this->events as $event) {
      if ($event->getUID() === NULL) {
        $event->setUID(uniqid().'@ps-webforge.com');
      }
      $ical .= $event->ical();
    }
    
    $ical .= $this->contentLine('END','VCALENDAR');
    return $ical;
  }
  
  public function icalTimezone() {
    $ical  = $this->contentLine('BEGIN','VTIMEZONE');
    $ical .= $this->contentLine('TZID','Europe/Berlin');
    $ical .= $this->contentLine('X-LIC-LOCATION','Europe/Berlin');
    $ical .= $this->contentLine('BEGIN','DAYLIGHT');
    $ical .= $this->contentLine('TZOFFSETFROM','+0100');
    $ical .= $this->contentLine('TZOFFSETTO','+0200');
    $ical .= $this->contentLine('TZNAME','CEST');
    $ical .= $this->contentLine('DTSTART','19700329T020000');
    $ical .= $this->contentLine('RRULE','FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU');
    $ical .= $this->contentLine('END','DAYLIGHT');
    $ical .= $this->contentLine('BEGIN','STANDARD');
    $ical .= $this->contentLine('TZOFFSETFROM','+0200');
    $ical .= $this->contentLine('TZOFFSETTO','+0100');
    $ical .= $this->contentLine('TZNAME','CET');
    $ical .= $this->contentLine('DTSTART','19701025T030000');
    $ical .= $this->contentLine('RRULE','FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU');
    $ical .= $this->contentLine('END','STANDARD');
    $ical .= $this->contentLine('END','VTIMEZONE');
    
    return $ical;
  }

  /**
   * @return Psc\Data\Ical\CalendarEvent
   */
  public function createEvent($title) {
    $event = new CalendarEvent($this, $title);
    $this->addEvent($event);
    return $event;
  }
  
  /**
   * @chainable
   */
  public function addEvent(CalendarEvent $event) {
    $this->events[] = $event;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getEvents() {
    return $this->events;
  }
  
  /**
   * @param Psc\Data\Ical\Builder $builder
   * @chainable
   */
  public function setBuilder(Builder $builder) {
    $this->builder = $builder;
    return $this;
  }

  /**
   * @return Psc\Data\Ical\Builder
   */
  public function getBuilder() {
    return $this->builder;
  }

  /**
   * @param string $calScale
   * @chainable
   */
  public function setCalScale($calScale) {
    $this->calScale = $calScale;
    return $this;
  }

  /**
   * @return string
   */
  public function getCalScale() {
    return $this->calScale;
  }
}
?>