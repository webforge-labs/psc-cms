<?php

namespace Psc\Test;

use Psc\Entities\Instancer;

class DatabaseTestCase extends \Psc\Doctrine\DatabaseTestCase {

  protected $languages = array('de');
  protected $language = 'de';

  private $container;

  protected function getContainer() {
    if (!isset($this->container)) {
      $this->container = new CMS\Container('Psc\Test\Controllers', $this->dc, $this->languages, $this->language);
    }

    return $this->container;
  }

  protected function initInstancer() {
    if (!isset($this->instancer)) {
      $this->instancer = new Instancer($this->getContainer(), $this->getResourceHelper()->getCommonDirectory());
    }
    return $this->instancer;
  }
}
