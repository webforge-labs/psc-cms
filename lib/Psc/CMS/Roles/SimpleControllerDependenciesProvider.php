<?php

namespace Psc\CMS\Roles;

use Psc\Doctrine\DCPackage;

class SimpleControllerDependenciesProvider implements ControllerDependenciesProvider {

  protected $dc;
  protected $container;

  public function __construct(DCPackage $dc, ControllerContainer $container) {
    $this->dc = $dc;
    $this->container = $container;
  }

  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage() {
    return $this->dc;
  }

  /**
   * @return Psc\CMS\Roles\ControllerContainer
   */
  public function getContainer() {
    return $this->container;
  }

  /**
   * @return Psc\CMS\Roles\SimpleContainer
   */
  public function getSimpleContainer() {
    return $this->container;
  }

  public function getTranslationContainer() {
    return $this->getContainer()->getTranslationContainer();
  }
}
