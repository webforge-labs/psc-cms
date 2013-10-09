<?php

namespace Psc;

use Psc\PSC;

/**
 * @group class:PSC
 */
class PSCTest extends \Psc\Code\Test\Base {
  
  public function testProjectIsDefined() {
    $this->assertInstanceOf('Webforge\Framework\Project', PSC::getProject());
    $this->assertSame($this->getProject(), PSC::getProject());
  }
  
  public function testErrorHandlerInstance() {
    $this->assertInstanceOf('Psc\Code\ErrorHandler', PSC::getErrorHandler());
  }
  
  public function testEnvironmentInstance() {
    $this->assertInstanceOf('Psc\Environment', PSC::getEnvironment());
  }
    
  public function testPscEventManagerIsReturnedFromPSC() {
    $this->assertInstanceOf('Psc\Code\Event\Manager', PSC::getEventManager());
  }
}
