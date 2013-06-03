<?php

namespace Psc\CMS\Roles;

class AbstractControllerContainerTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Roles\\AbstractControllerContainer';
    parent::setUp();

    $this->dc = $this->doublesManager->createDoctrinePackageMock();
    $this->languages = array('de');
    $this->language = 'de';

    $this->container = new \Psc\Test\CMS\Container('Psc\Test\Controllers', $this->dc, $this->languages, $this->language);
  }

  public function testPre() {
    $this->assertInstanceOf($this->chainClass, $this->container);
  }


}
