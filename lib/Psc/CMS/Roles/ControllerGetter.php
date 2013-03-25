<?php

namespace Psc\CMS\Roles;

interface ControllerGetter {

  /**
   * @see Psc\CMS\Controller\Factory
   */
  public function getController($controllerName);

}
