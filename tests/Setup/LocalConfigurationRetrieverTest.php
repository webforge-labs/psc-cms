<?php

namespace Psc\Setup;

/**
 * @group class:Psc\Setup\LocalConfigurationRetriever
 */
class LocalConfigurationRetrieverTest extends \Psc\Code\Test\Base {
  
  protected $localConfigurationRetriever;
  
  public function setUp() {
    $this->chainClass = 'Psc\Setup\LocalConfigurationRetriever';
    parent::setUp();
    $this->localConfigurationRetriever = new LocalConfigurationRetriever();
  }
  
  public function testAcceptance() {
    $this->localConfigurationRetriever->retrieveIni('post_max_size', ini_get('post_max_size'));
    $this->localConfigurationRetriever->retrieveIni('include_path', ini_get('include_path'));
  }
}
?>