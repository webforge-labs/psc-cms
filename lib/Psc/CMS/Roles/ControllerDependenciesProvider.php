<?php

namespace Psc\CMS\Roles;

interface ControllerDependenciesProvider {

  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage();

  /**
   * @return Psc\CMS\Roles\SimpleContainer
   */
  public function getSimpleContainer();

  /**
   * @return Psc\CMS\Roles\ControllerContainer
   */
  public function getContainer();

}
