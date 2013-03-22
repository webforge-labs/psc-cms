<?php

namespace Psc\CMS\Controller;

use Psc\Code\Code;
use Psc\Code\Generate\GClass;

/**
 * 
 * 1. needs to resvole names to controller FQNs (check)
 *    (needs the package namespace for this to simplify, but the webforge container could create such a factory for example)
 * 
 * 2. needs to inject dependencies into the constructor (or through interfaces) to the controller
 *    How do we provide a flexible way to inject everything we need in project => (Simple-)Container aka ProjectContainer?
 * 
 * 3. needs to be called from INSIDE other controllers to help with cross referencings shit
 *    => needs to be in the container?
 * 
 * 
 */
class Factory {

  /**
   * Mappings from $names to ControllerFQNs
   * 
   * @var GClass[]
   */
  protected $classes = array();

  /**
   * @var array
   */
  protected $controllers = array();

  /**
   * @var string
   */
  protected $defaultNamespace;

  public function __construct($defaultNamespace) {
    $this->defaultNamespace = $defaultNamespace;
  }

  /**
   *  @return Controller-Instance
   */
  public function getController($controllerName) {
    $controllerClass = $this->getControllerGClass($controllerName);

    return $controllerClass->newInstance();
  }

  /**
   * @return GClass
   */
  protected function getControllerGClass($controllerName) {
    if (isset($this->classes[$controllerName])) {
      return $this->classes[$controllerName];
    }

    $gClass = new GClass();
    $gClass->setClassName($controllerName.'Controller');
    $gClass->setNamespace($this->getDefaultNamespace());

    return $gClass;
  }


  /**
   * 
   * @param string $entityFQN always the full qualified name
   * @return Controller-Instance
   */
  public function getControllerForEntity($entityFQN) {
    return $this->getController(Code::getClassName($entityFQN));
  }

  /**
   * Returns the default Namespace where to search for controllers
   * 
   * without \ before and after the namespace
   */
  protected function getDefaultNamespace() {
    return $this->defaultNamespace;
  }

  public function setControllerFQN($controllerName, $controllerFQN) {
    $this->classes[$controllerName] = new GClass($controllerFQN);
    return $this;
  }
}
