<?php

namespace Psc\CMS\Roles;

use Webforge\Framework\Package\Package;
use Psc\Doctrine\DCPackage;
use Psc\TPL\ContentStream\Converter AS ContentStreamConverter;

abstract class AbstractContainer extends AbstractControllerContainer implements Container, \Psc\TPL\ContentStream\Context {

  protected $defaultControllersNamespace;

  public function __construct($controllersNamespace, DCPackage $dc, Array $languages, $language,  ContentStreamConverter $contentStreamConverter = NULL) {
    parent::__construct($controllersNamespace ?: $this->defaultControllersNamespace, $dc, $languages, $language, $contentStreamConverter);
  }

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
