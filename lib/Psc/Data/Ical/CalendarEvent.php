<?php

namespace Psc\Data\Ical;

use Psc\DateTime\Date;
use Psc\DateTime\DateTime;
use Psc\DateTime\DateInterval;

/**
 *
 * unser CalendarEvent kann noch nicht besonders viel.
 * Es fehlen:
 *   - startTime-EndTime
 *   - Reminders
 *   - Location
 *   - Attendetes usw
 *   - Categories
 *
 * Klasse kann bis jetzt nur one-day Events
 */
class CalendarEvent extends \Psc\Data\Ical\Base {
  
  /**
   * @var Psc\Data\Ical\Calendar
   */
  protected $calendar;
  
  /**
   * @var string
   */
  protected $title;
  
  /**
   * @var Psc\DateTime\DateInterval
   */
  protected $recurrence;
  
  /**
   * Der Tag an dem das Event stattfindet
   * 
   * @var Psc\DateTime\Date
   */
  protected $date;
  
  /**
   * Zeigt an ob dies ein ganztägiges Event ist
   * @var bool
   */
  protected $allDay = FALSE;
  
  
  protected $description;
  protected $location;
  protected $created;
  protected $modified;
  protected $sequence = 0; // edit-counter
  protected $status = 'CONFIRMED';
  protected $uid;
  
  public function __construct(Calendar $calendar, $title) {
    $this->title = $title;
    $this->calendar = $calendar;
    $this->created = DateTime::now();
    $this->stamp = DateTime::now(); // Kein plan was der unterschied zu created ist
    $this->modified = DateTime::now();
  }
  
  
  public function ical() {
    $ical  = $this->contentLine('BEGIN', 'VEVENT');
    if ($this->allDay) {
      $ical .= $this->contentLine('DTSTART', $this->icalDate($this->date), array('VALUE'=>'DATE'));
      $ical .= $this->contentLine('DTEND', $this->icalDate(DateInterval::create('1 DAY')->addTo($this->date)), array('VALUE'=>'DATE'));
    }
    $ical .= $this->icalRecurrence($this->recurrence);
    $ical .= $this->contentLine('DTSTAMP', $this->icalDateTime($this->stamp));
    $ical .= $this->contentLine('UID', $this->getUID());
    $ical .= $this->contentLine('CREATED', $this->icalDateTime($this->created));
    $ical .= $this->contentLine('DESCRIPTION', $this->description);
    $ical .= $this->contentLine('LAST-MODIFIED', $this->icalDateTime($this->modified));
    $ical .= $this->contentLine('LOCATION', $this->location); 
    $ical .= $this->contentLine('SEQUENCE', $this->sequence);
    $ical .= $this->contentLine('STATUS', $this->status);
    $ical .= $this->contentLine('SUMMARY', $this->title);
    $ical .= $this->contentLine('TRANSP', 'TRANSPARENT');
    $ical .= $this->contentLine('END', 'VEVENT');
    
    return $ical;
  }
  
  public function getBuilder() {
    return $this->calendar->getBuilder();
  }
  
  /**
   * @param bool $allDay
   * @chainable
   */
  public function setAllDay($allDay) {
    $this->allDay = $allDay;
    return $this;
  }

  /**
   * @return bool
   */
  public function getAllDay() {
    return $this->allDay;
  }

/**
   * @param Psc\DateTime\Date $date
   * @chainable
   */
  public function setDate(Date $date) {
    $this->date = $date;
    $this->allDay = TRUE;
    return $this;
  }

  /**
   * @return Psc\DateTime\Date
   */
  public function getDate() {
    return $this->date;
  }

/**
   * @param Psc\DateTime\DateInterval $recurrence
   * @chainable
   */
  public function setRecurrence(DateInterval $recurrence) {
    $this->recurrence = $recurrence;
    return $this;
  }

  /**
   * @return Psc\DateTime\DateInterval
   */
  public function getRecurrence() {
    return $this->recurrence;
  }

/**
   * @param string $title
   * @chainable
   */
  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->title;
  }
  
  /**
   * @param DateTime $modified
   * @chainable
   */
  public function setModified(DateTime $modified) {
    $this->modified = $modified;
    return $this;
  }

  /**
   * @return DateTime
   */
  public function getModified() {
    return $this->modified;
  }

  /**
   * @param DateTime $created
   * @chainable
   */
  public function setCreated(DateTime $created) {
    $this->created = $created;
    return $this;
  }

  /**
   * @return DateTime
   */
  public function getCreated() {
    return $this->created;
  }

  /**
   * @param string $uid
   * @chainable
   */
  public function setUID($uid) {
    $this->uid = $uid;
    return $this;
  }

  /**
   *
   * 'ho457t8vphmuud2qjmfaej73l8@google.com';
   * @return string
   */
  public function getUID() {
    return $this->uid;
  }
  
  /**
   * @param DateTime $stamp
   * @chainable
   */
  public function setStamp(DateTime $stamp) {
    $this->stamp = $stamp;
    return $this;
  }

  /**
   * @return DateTime
   */
  public function getStamp() {
    return $this->stamp;
  }


}
?>