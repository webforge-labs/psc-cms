<?php

namespace Psc\CMS\Roles;

interface ControllerContainer extends SimpleContainer, ControllerGetter, ControllerDependenciesProvider {

  public function getControllerFactory();

}
