<?php

namespace Psc\CMS;

use Psc\CMS\ResourceHandler;
use Psc\Net\HTTP\Request;

/**
 * @group net-service
 * @group class:Psc\CMS\ResourceHandler
 */
class ResourceHandlerTest extends \Psc\Code\Test\Base {
  
  /**
   * @dataProvider provideRequestHandling
   */
  public function testRequestHandling($resource, $contentType) {
    $resourceHandler = new ResourceHandler($this->createResourceManager());
    $resourceHandler->init();
    
    // ein schöner request mit user agent, url usw
    $request = $this->getDoublesManager()->createHTTPRequest('GET',$resource);
    
    $response = $resourceHandler->handle($request);
    $this->assertInstanceOf('Psc\Net\HTTP\Response',$response);
    $this->assertEquals(200,$response->getCode(), $response->debug());
    $this->assertNotEmpty($response->getBody());
    $this->assertEquals($contentType,$response->getHeader()->getField('Content-Type'));
  }
  
  public static function provideRequestHandling() {
    $tests = array();
    
    $tests[] = array('/css/jquery-ui/smoothness/images/ui-bg_highlight-soft_75_cccccc_1x100.png', 'image/png');
    $tests[] = array('/css/reset.css', 'text/css');
    $tests[] = array('/js/cms/ui.comboBox.js','text/javascript');
    $tests[] = array('/js/fileupload/jquery.iframe-transport.js','text/javascript');
    $tests[] = array('/js/fileupload/jquery.iframe-transport.js','text/javascript');
    $tests[] = array('/img/cms/ajax-spinner-small.gif','image/gif');
    $tests[] = array('/css/jquery-ui/smoothness/jquery-ui-1.8.12.custom.css', 'text/css');
    
    
    return $tests;
  }
  
  
  /**
   * @expectedException Psc\Net\UnhandledRequestException
   */
  public function testRequestHandling_withoutInitUsed() {
    $resourceHandler = new ResourceHandler(new ResourceManager());
    $resourceHandler->handle($this->getDoublesManager()->createHTTPRequest('GET','/js/cms/ui.comboBox.js'));
  }

  /**
   * @expectedException Psc\Net\UnhandledRequestException
   */
  public function testRequestHandling_withWrongRequestResource() {
    $resourceHandler = new ResourceHandler(new ResourceManager());
    $resourceHandler->handle($this->getDoublesManager()->createHTTPRequest('GET','/blubb/ui.comboBox.js'));
  }

  /**
   * @expectedException Psc\Exception
   */
  public function testRequestHandling_withWrongMethod() {
    $resourceHandler = new ResourceHandler(new ResourceManager());
    $resourceHandler->handle($this->getDoublesManager()->createHTTPRequest('POST','/blubb/ui.comboBox.js'));
  }

  protected function createResourceManager() {
    return new ResourceManager();
  }

}
?>