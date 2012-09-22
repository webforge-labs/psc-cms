<?php

namespace Psc\Setup;

/**
 * @group class:Psc\Setup\RemoteConfigurationRetriever
 */
class RemoteConfigurationRetrieverTest extends \Psc\Code\Test\Base {
  
  protected $remoteConfigurationRetriever;
  
  public function setUp() {
    $this->chainClass = 'Psc\Setup\RemoteConfigurationRetriever';
    parent::setUp();
    //$this->remoteConfigurationRetriever = new RemoteConfigurationRetriever();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>