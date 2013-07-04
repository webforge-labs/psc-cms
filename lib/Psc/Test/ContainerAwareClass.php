<?php

namespace Psc\Test;

use Psc\CMS\ContainerAware;
use Psc\CMS\Roles\Container;

class ContainerAwareClass implements ContainerAware{

  public $container;

  public function setContainer(Container $container) {
    $this->container = $container;
  }
}
