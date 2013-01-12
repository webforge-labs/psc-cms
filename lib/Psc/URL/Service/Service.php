<?php

namespace Psc\URL\Service;

use \Psc\Object,
    \Closure
;

/**
 *
 *
 * Wenn man einen Controller setzt muss man schon selbst aufpassen, was man da für mist macht.
 * Wenn der Service nämlich eine Funktion durchlässt, dies nciht gibt fällt hier call_user_func_array auf die Nase
 *
 * @TODO validierung für einzelne methoden get identifier etc, wäre schon geil (dann würden die controller viel einfacher werden)
 */
class Service extends \Psc\Object {
  
  protected $name;
  
  /**
   * @var Psc\URL\Service\Controller
   */
  protected $controller;
  
  /**
   * @var \Closure
   */
  protected $action;
  
  public function __construct($name) {
    $this->name = $name;
  }
  
  public function process(Call $call) {
    if (isset($this->controller)) {
      return $this->processWithController($call);
    }
    
    if (isset($this->action)) {
      return $this->processWithAction($call);
    }
    
    throw new \Psc\Exception('Inconsistent State: weder Controller noch Action ist gesetzt, aber process() wurde aufgerufen');
  }
  
  protected function processWithController(Call $call) {
    return call_user_func_array(array($this->controller,$call->getName()), $call->getParameters());
  }
  
  protected function processWithAction(Call $call) {
    $p = $call->getParameters();
    array_unshift($p, $call->getName());
    return call_user_func_array($this->action, $p);
  }
  
  public function setAction(Closure $action) {
    $this->action = $action;
    return $this;
  }
  
  /**
   * @param Psc\URL\Service\Controller $controller
   * @chainable
   */
  public function setController(Controller $controller) {
    $this->controller = $controller;
    return $this;
  }

  /**
   * @return Psc\URL\Service\Controller
   */
  public function getController() {
    return $this->controller;
  }
}

?>