<?php

namespace Psc\Net;

/**
 * @group class:Psc\Net\ServiceErrorPackage
 */
class ServiceErrorPackageTest extends \Psc\Code\Test\Base {
  
  protected $package;
  
  public function setUp() {
    $this->chainClass = 'Psc\Net\ServiceErrorPackage';
    parent::setUp();
    $this->package = new ServiceErrorPackage($this);
  }
  
  /**
   * @expectedException Psc\Net\HTTP\HTTPException
   */
  public function testAcceptance() {
    throw $this->package->invalidArgument(__METHOD__, 'keins', 'unexpected', $msg = 'Der Parameter keins ist gesetzt mit dem Wert "unexpected" und das wollen wir nicht. Deshalb steigen wir mit einer HTTPException aus');
  }
}
?>