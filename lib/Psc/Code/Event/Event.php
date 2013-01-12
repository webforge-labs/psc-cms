<?php

namespace Psc\Code\Event;

use \stdClass;
use Psc\Code\Code;

class Event extends \Psc\SimpleObject {
  
  /**
   * @var stdClass
   */
  protected $data;
  
  /**
   * @var mixed
   */
  protected $target;
  
  /**
   * Die eindeutige Identifizierung des Events in seinem Namespace
   *
   * Aber es kann natürlich mehrere Event-Instanzen mit demselben Identifier geben
   * @var string
   */
  protected $name;
  
  /**
   * Namespace des Events
   *
   * der Identifier ist dann $namespace.$identifier
   * @var string
   */
  protected $namespace;
  
  /**
   * Wurde das Event von einem Subscriber behandelt?
   *
   * @var bool
   */
  protected $processed = FALSE;
  
  /**
   * Erstellt ein neues Event
   *
   * @param mixed $target kann das Objekt sein von dem das Event ausgeht (siehe jQuery)
   */
  public function __construct($nsname, $target = NULL) {
    $this->target = $target;
    $this->data = new stdClass();
    
    if (mb_strpos($nsname,'.') !== FALSE) {
      list($this->namespace,$this->name) = explode('.',$nsname,2);
    } else {
      $this->name = $nsname;
    }
  }
  
  /**
   * @return Event
   */
  public static function factory($nsname, $target = NULL) {
    return new static($nsname, $target);
  }
  
  /**
   * Ist das Event vom Typ $identifier ?
   * @return bool
   */
  public function is($identifier) {
    return $this->getIdentifier() === $identifier;
  }
  
  /**
   * Gibt den Identifier zurück
   *
   * dies ist standardmäßig mit Namespace
   */
  public function getIdentifier() {
    return $this->namespace ? $this->namespace.'.'.$this->name : $this->name;
  }
  
  /** 
   * 
   * @param string $name erste Buchstabe groß dann Camelcase, ohne .
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * Gibt den Namen des Events ohne Namespace zurück
   *
   * erste Buchstabe groß
   * @return string
   */
  public function getName() {
    return $this->name;
  }
  
  public function getNamespace() {
    return $this->namespace;
  }
  
  public function setNamespace($ns) {
    $this->namespace = $ns;
    return $this;
  }
  
  /**
   * @param mixed $data
   * @chainable
   */
  public function setData($data) {
    $this->data = (object) $data;
    return $this;
  }

  /**
   * @return object
   */
  public function getData() {
    return $this->data;
  }

  /**
   * @param mixed $target
   * @chainable
   */
  public function setTarget($target) {
    $this->target = $target;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getTarget() {
    return $this->target;
  }

  /**
   * @param bool $processed
   * @chainable
   */
  public function setProcessed($processed) {
    $this->processed = (bool) $processed;
    return $this;
  }

  /**
   * @return bool
   */
  public function isProcessed() {
    return $this->processed;
  }

  public function __toString() {
    try {
      $str = 'Event['.$this->getIdentifier().'] '."\n";
      $str .= 'Target: '.Code::varInfo($this->target)."\n";
      $str .= 'Daten: '.\Psc\Doctrine\Helper::getDump($this->data, $maxDepth = 1)."\n";
    } catch (\Exception $e) {
      print $e;
      exit;
    }
    return $str;
  }
}
?>