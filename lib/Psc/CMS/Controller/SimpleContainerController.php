<?php

namespace Psc\CMS\Controller;

use Psc\Doctrine\DCPackage;
use Psc\CMS\EntityViewPackage;
use Psc\Form\ValidationPackage;
use Psc\Net\ServiceErrorPackage;
use Psc\CMS\Roles\SimpleContainer;

abstract class SimpleContainerController extends AbstractEntityController {

  /**
   * @var Psc\CMS\Roles\SimpleContainer
   */
  protected $container;
  
  public function __construct(DCPackage $dc = NULL, EntityViewPackage $ev = NULL, ValidationPackage $v = NULL, ServiceErrorPackage $err = NULL, SimpleContainer $container = NULL) {
    $this->container = $container;
    parent::__construct($dc, $ev, $v, $err);
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

  protected function injectRevision(Array $query = NULL) {
    if (is_array($query) && isset($query['revision'])) {
      $this->container->setRevision(trim($query['revision']));
    }
  }
  
  public function getLanguages() {
    return $this->container->getLanguages();
  }
  
  public function getLanguage() {
    return $this->container->getLanguage();
  }
}
?>