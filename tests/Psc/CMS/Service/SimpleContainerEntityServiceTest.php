<?php

namespace Psc\CMS\Service;

class SimpleContainerEntityServiceTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Service\\SimpleContainerEntityService';
    parent::setUp();

    $this->languages = array('de', 'jp');
    $this->language = 'jp';
    $this->dc = $this->doublesManager->createDoctrinePackageMock();
    $this->container = $this->getMockForAbstractClass('Psc\CMS\Roles\AbstractControllerContainer', array($this->dc, $this->languages, $this->language));

  }

  public function testCanConstructTheEntityServiceFromItsParameters() {
    $service = $this->assertInstanceOf($this->chainClass, 
      new SimpleContainerEntityService(
        $this->dc, 
        $this->container,
        $this->getProject()
      )
    );
  }
}
