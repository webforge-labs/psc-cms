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

  /**
   * @param string|ControllerFactory $controllerFactoryInfo if this is a string its treated as the defaultNamespace. If it is a controllerFactory its treated as injection
   */
  public function __construct($controllerFactoryInfo, DCPackage $dc, Array $languages, $language,  ContentStreamConverter $contentStreamConverter = NULL) {
    if ($controllerFactoryInfo instanceof ControllerFactory) {
      $this->controllerFactory = $controllerFactoryInfo;
    } else {
      $this->controllerFactory = new ControllerFactory((string) $controllerFactoryInfo, $this);
    }
    
    parent::__construct($dc, $languages, $language, $contentStreamConverter);
  }

  public function getController($controllerName) {
    return $this->controllerFactory->getController($controllerName);
  }

  /**
   * @return Psc\CMS\Controller\Factory
   */
  public function getControllerFactory() {
    return $this->controllerFactory;
  }

  /**
   * @return Psc\CMS\Roles\SimpleContainer
   */
  public function getSimpleContainer() {
    return $this;
  }

  /**
   * @return Psc\CMS\Roles\Container
   */
  public function getContainer() {
    return $this;
  }
}
