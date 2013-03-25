<?php

namespace Psc\CMS\Roles;

abstract class AbstractControllerContainer extends AbstractSimpleContainer implements ControllerContainer {

  public function getSimpleContainer() {
    return $this;
  }

}
