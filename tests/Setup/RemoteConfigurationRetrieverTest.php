<?php

namespace Psc\Setup;

/**
 * @group class:Psc\Setup\RemoteConfigurationRetriever
 */
class RemoteConfigurationRetrieverTest extends \Psc\Code\Test\Base {
  
  protected $retriever;
  protected $dispatcher, $iniResponse;
  
  public function setUp() {
    $this->chainClass = 'Psc\Setup\RemoteConfigurationRetriever';
    parent::setUp();
    
    $this->iniResponse = $this->doublesManager->createURLResponse(json_encode(ini_get_all()), array('Content-Type','application\json'));
  }
  
  public function testAcceptance() {
    $this->dispatcher = $this->doublesManager->RequestDispatcher()
      ->expectReturnsResponseOnDispatch($this->iniResponse, $this->once())
      ->build();
    
    $this->createRetriever();
    
    // vergleiche unsere gemockten ini values
    foreach (array_slice(ini_get_all(),0,40) as $iniName => $iniInfo) {
      $iniValue = $iniInfo['local_value'];
      $this->assertEquals($iniValue, $this->retriever->retrieveIni($iniName), 'iniValue for '.$iniName.' is not correct');
    }
  }

  protected function createRetriever() {
    $this->retriever = new RemoteConfigurationRetriever('/is/faked/test.php', $this->dispatcher);
  }
}
?>