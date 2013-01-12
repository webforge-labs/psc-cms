<?php

namespace Psc\UI;

use Doctrine\Common\Collections\Collection;

/**
 * 
 */
class Calendar extends \Psc\HTML\JooseBase {
  
  /**
   * @var Doctrine\Common\Collections\Collection<Psc\UI\CalendarEvent>
   */
  protected $events;
  
  /**
   * @var string
   */
  protected $eventJooseClass = 'Psc.CalendarEvent';
  
  public function __construct($region = 'de') {
    $this->events = new \Psc\Data\ArrayCollection;
    parent::__construct('Psc.UI.Calendar', array('region'=>$region));
  }
  
  protected function doInit() {
    $this->html = new HTMLTag('div', '<img src="/img/ajax-loader.gif" alt="loading.." />', array('class'=>'\Psc\calendar'));
    
    // kewl wäre auch ein JooseExport Interface, welches die Klasse direkt mitgibt
    $events = array();
    foreach ($this->events as $event) {
      $events[] = $this->createJooseSnippet(
        $this->eventJooseClass,
        $event->export()
      ); 
    }
    
    $this->autoLoadJoose(
      $this->createJooseSnippet(
        $this->jooseClass,
        array(
          'widget'=>$this->widgetSelector(),
          'events'=>$events
        )
      )->addUse($this->eventJooseClass) // schade, dass geht noch nicht automatisch, wegen dem SonderFall, dass wir die Events in einem Array ham
    );
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Psc\UI\CalendarEvent>
   */
  public function getEvents() {
    return $this->events;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Psc\UI\CalendarEvent> $events
   */
  public function setEvents(Collection $events) {
    $this->events = $events;
    return $this;
  }
  
  /**
   * @param integer $key 0-based
   * @return Psc\UI\CalendarEvent|NULL
   */
  public function getEvent($key) {
    return $this->events->containsKey($key, $this->events) ? $this->events->get($key) : NULL;
  }
  
  /**
   * @param Psc\UI\CalendarEvent $event
   * @chainable
   */
  public function addEvent(CalendarEvent $event) {
    $this->events->add($event);
    return $this;
  }
  
  /**
   * @param Psc\UI\CalendarEvent $event
   * @chainable
   */
  public function removeEvent(CalendarEvent $event) {
    if ($this->contains($event)) {
      $this->removeElement($event);
    }
    return $this;
  }
  
  /**
   * @param Psc\UI\CalendarEvent $event
   * @return bool
   */
  public function hasEvent(CalendarEvent $event) {
    return $this->events->contains($event);
  }
  
  /**
   * @param string $eventJooseClass
   */
  public function setEventJooseClass($eventJooseClass) {
    $this->eventJooseClass = $eventJooseClass;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getEventJooseClass() {
    return $this->eventJooseClass;
  }
}
?>