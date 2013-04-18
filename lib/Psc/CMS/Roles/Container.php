<?php

namespace Psc\CMS\Roles;

use Webforge\Framework\Package\Package;

interface Container extends ControllerContainer {

  public function setPackage(Package $package);
  public function getPackage();

  /**
   * 
   * $container->getPackageDir('files/cache/images/');
   * @param string $sub with forward slashes relative to the package root and with a forward slash at the end not at the start
   * @return Webforge\Common\System\Dir
   */
  public function getPackageDir($sub);
}
