<?php

namespace Psc\CMS\Roles;

use Psc\Doctrine\DCPackageProvider;

interface ControllerDependenciesProvider extends DCPackageProvider {

  /**
   * @return Psc\CMS\Roles\SimpleContainer
   */
  public function getSimpleContainer();

  /**
   * @return Psc\CMS\Roles\ControllerContainer
   */
  public function getContainer();

}
