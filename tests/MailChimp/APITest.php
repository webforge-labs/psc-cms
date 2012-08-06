<?php

namespace Psc\MailChimp;

use Psc\URL\Request;
use Psc\URL\Response;

/**
 * @group class:Psc\MailChimp\API
 */
class APITest extends \Psc\Code\Test\Base {
  
  protected $api, $request, $service;
  
  public function setUp() {
    $this->chainClass = 'Psc\MailChimp\API';
    parent::setUp();
    
    $apiKey = \Psc\PSC::getProjectsFactory()->getHostConfig()->req('mailchimp.test.apiKey');
    $this->api = new API($apiKey);
    $this->service = $this->getMock('Psc\URL\SimpleService', array('dispatch'));
    $this->mockedApi = new API($apiKey, $this->service);
  }
  
  public function testAPIKeyIsset() {
    $this->assertNotEmpty($this->api->getApiKey());
  }
  
  public function testSubscribeListDispatchesTheRightRequest() {
    $this->expectDispatchRequest(new Response('true', new \Psc\URL\HTTP\Header()));
    
    $this->mockedApi->listSubscribe('listid', 'info@ps-webforge.com');
    
    $json = $this->assertCommonRequest($this->request);
    $this->assertEquals('info@ps-webforge.com', $json->email_address);
    $this->assertEquals('listid', $json->id);
    $this->assertEquals('text', $json->email_type);
  }

  public function testSubscribeListThrowsMailchimpExceptionIfResponseIsFalse() {
    $this->expectDispatchRequest(new Response('false', new \Psc\URL\HTTP\Header()));
    $this->setExpectedException('Psc\MailChimp\Exception');
    
    $this->mockedApi->listSubscribe('listid', 'info@ps-webforge.com');
    
  }
  
  protected function expectDispatchRequest(Response $response) {
    $that = $this;
    $this->service->expects($this->once())->method('dispatch')->with($this->isInstanceOf('Psc\URL\Request'))
      ->will($this->returnCallback(function ($request) use ($that, $response) {
          $that->catchRequest($request);
          $request->setResponse($response);
          return $response;
        })
      );
  }
  public function catchRequest(Request $request) {
    $this->request = $request;
  }
  
  protected function assertCommonRequest(Request $request) {
    $json = $this->test->json($request->getData());
    $this->assertNotEmpty($json->apikey);
    return $json;
  }
  
  
  public function testAcceptanceListSubscribe() {
    $this->markTestSkipped('sends a confirmation mail');
    $this->assertTrue($this->api->listSubscribe('717e8e769e','info@ps-webforge.com'));
  }
}
?>