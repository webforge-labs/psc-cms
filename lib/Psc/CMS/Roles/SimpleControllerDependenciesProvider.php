<?php

namespace Psc\CMS\Roles;

use Psc\Doctrine\DCPackage;

class SimpleControllerDependenciesProvider implements ControllerDependenciesProvider {

  protected $dc;
  protected $container;

  public function __construct(DCPackage $dc, SimpleContainer $container) {
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
   * @return Psc\CMS\Roles\SimpleContainer
   */
  public function getSimpleContainer() {
    return $this->container;
  }
}
