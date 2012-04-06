<?php

namespace Psc\CMS;

use Psc\CMS\Service;
use Psc\PSC;
use Psc\Net\ServiceRequest;
use Psc\Net\ServiceResponse;

/**
 * @group net-service
 */
class ServiceTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service';
    parent::setUp();
  }

  public function testConstruct() {
    $service = $this->createService($pr = PSC::getProject());
    $this->assertSame($pr,$service->getProject());
  }
  
  public function testInterface() {
    $svc = $this->createService();
    $this->assertInternalType('bool',$svc->isResponsibleFor(new ServiceRequest('GET',array('none'))));
  }

  public function createService($project = NULL) {
    return new Service($project ?: PSC::getProject());
  }
}
?>