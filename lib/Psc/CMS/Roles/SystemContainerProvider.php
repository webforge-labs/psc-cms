<?php

namespace Psc\CMS\Roles;

use Webforge\Common\System\Container as SystemContainer;

interface SystemContainerProvider {

  /**
   * @return Webforge\Common\System\Container
   */
  public function getSystemContainer();
  
}
