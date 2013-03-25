<?php

namespace Psc\CMS\Roles;

use Psc\CMS\Controller\Factory as ControllerFactory;
use Psc\Doctrine\DCPackage;
use Psc\TPL\ContentStream\Converter as ContentStreamConverter;
use Psc\Image\Manager as ImageManager;

abstract class AbstractControllerContainer extends AbstractSimpleContainer implements ControllerContainer {

  /**
   * @var Psc\CMS\Controller\Factory
   */
  protected $controllerFactory;

  public function __construct($controllerDefaultNamespace, DCPackage $dc, Array $languages, $language,  ContentStreamConverter $contentStreamConverter = NULL) {
    $this->controllerFactory = new ControllerFactory($controllerDefaultNamespace, $this);
    parent::__construct($dc, $languages, $language, $contentStreamConverter);
  }

  public function getController($controllerName) {
    return $this->controllerFactory->getController($controllerName);
  }

  public function getSimpleContainer() {
    return $this;
  }

  public function getContainer() {
    return $this;
  }
}
