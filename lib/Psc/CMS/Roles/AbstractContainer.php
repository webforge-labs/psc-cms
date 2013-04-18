<?php

namespace Psc\CMS\Roles;

use Webforge\Framework\Package\Package;

abstract class AbstractContainer extends AbstractControllerContainer implements Container, \Psc\TPL\ContentStream\Context {

  /**
   * @var Webforge\Framework\Package\Package
   */
  protected $package;

  public function setPackage(Package $package) {
    $this->package = $package;
  }

  public function getPackage() {
    if (!isset($this->package)) {
      $this->package = $GLOBALS['env']['container']->webforge->getLocalPackage();
    }
    
    return $this->package;
  }

  public function getPackageDir($sub) {
    return $this->getPackage()->getRootDirectory()->sub($sub);
  }
}
