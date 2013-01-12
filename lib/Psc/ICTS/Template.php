<?php

namespace Psc\ICTS;

use \Psc\ICTS\Data;

/**
 * Ist der name im Template gesetzt, wird im gesetzten binder bei __construct() der prefix gesetzt
 */
abstract class Template extends \Psc\Object {
  
  const THROW_EXCEPTION = Data::THROW_EXCEPTION;
  
  protected $root = FALSE;
  
  /**
   * @var string|array oder was was zu array gecastet werden kann
   */
  protected $name;

  /**
   * @var \Psc\ICTS\Binder
   */
  protected $binder;
  
  /**
   * @var Template
   */
  protected $parentTemplate;
  
  public function __construct(Template $parentTemplate) {
    $this->parentTemplate = $parentTemplate;
    $this->binder = $this->parentTemplate->createBinder($this); // hier clonen damit wir die prefixe richtig einordnen
    
    if (!$this->isRoot())
      $this->setUp();
  }
  
  /**
   * ist noch nicht ganz klar, wann es wirklich aufgerufen werden soll
   * erstmal: setUp im Konstruktor
   *
   * init() vor get()
   */
  protected function setUp() {}

  public function g($keys,$default=NULL) {
    return $this->binder->g($keys,$default);
  }

  public function b($keys,$default=NULL) {
    return $this->binder->b($keys,$default);
  }
  
  public function addPrefix($prefix) {
    $this->binder->addPrefix($prefix);
    return $this;
  }

  public function removePrefix() {
    $this->binder->removePrefix();
    return $this;
  }

  public function createBinder(Template $child) {
    $binder = clone $this->binder;
    
    if ($child->getName() != NULL) {
      $binder->addPrefix($child->getName());
    }
    
    return $binder;
  }
  
  /**
   * Gibt den Inhalt des Templates zurück
   */
  abstract public function get();

  public function isRoot() {
    return (bool) $this->root;
  }
}