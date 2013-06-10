<?php

namespace Psc\CMS\Controller;

use Psc\CMS\Roles\ControllerContainer;
use Psc\Doctrine\DCPackage;
use Psc\CMS\EntityViewPackage;
use Psc\Form\ValidationPackage;
use Psc\Net\ServiceErrorPackage;
use Psc\CMS\Roles\SimpleContainer;

abstract class ContainerController extends SimpleContainerController {

  /**
   * @var Psc\CMS\Roles\ControllerContainer
   */
  protected $container;

  public function __construct(DCPackage $dc, ControllerContainer $container, EntityViewPackage $ev = NULL, ValidationPackage $v = NULL, ServiceErrorPackage $err = NULL) {
    parent::__construct($container->getTranslationContainer(), $dc, $ev, $v, $err, $container);
  }

  /**
   * @param Psc\CMS\Roles\ControllerContainer $container
   */
  public function setContainer(SimpleContainer $container) {
    if (!($container instanceof ControllerContainer)) {
      throw $this->invalidArgument(1, $container, 'Psc\CMS\Roles\ControllerContainer;', __FUNCTION__);
    }

    $this->container = $container;
    return $this;
  }

  public function getController($controllerName) {
    return $this->container->getController($controllerName);
  }
}
