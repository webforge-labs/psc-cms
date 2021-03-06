<?php

namespace Psc\CMS\Service;

use Psc\Doctrine\DCPackage;
use Webforge\Framework\Project;
use Psc\CMS\Roles\ControllerContainer;
use Psc\CMS\Controller\SimpleContainerController;
use Psc\CMS\Controller\Factory as ControllerFactory;
use Psc\CMS\Roles\SimpleControllerDependenciesProvider;

class SimpleContainerEntityService extends EntityService {

  /**
   * @var Psc\CMS\Roles\SimpleContainer
   */
  protected $container;
  
  public function __construct(DCPackage $dc, ControllerContainer $container, Project $project, $prefixPart = 'entities') {
    $this->container = $container;

    $factory = new ControllerFactory(
      $project->getNamespace().'\\Controllers',
      new SimpleControllerDependenciesProvider($dc, $container)
    );

    parent::__construct($dc, $factory, $project, $prefixPart);
  }
  
  public function getEntityController($part) {
    $controller = parent::getEntityController($part);
    
    if ($controller instanceof SimpleContainerController) {
      $controller->setContainer($this->getContainer());
    }
    
    return $controller;
  }
  
  /**
   * @param Psc\CMS\Roles\SimpleContainer $container
   */
  public function setContainer(SimpleContainer $container) {
    $this->container = $container;
    return $this;
  }
  
  /**
   * @return Psc\CMS\Roles\SimpleContainer
   */
  public function getContainer() {
    return $this->container;
  }
}
?>