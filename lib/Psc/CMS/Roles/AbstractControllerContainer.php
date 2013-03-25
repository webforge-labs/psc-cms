<?php

namespace Psc\CMS\Roles;

abstract class AbstractControllerContainer extends AbstractSimpleContainer implements ControllerGetter, ControllerDependenciesProvider {

  public function getSimpleContainer() {
    return $this;
  }

}
