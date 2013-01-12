<?php

namespace Psc\URL;

/**
 * @group class:Psc\URL\RequestDispatcher
 */
class RequestDispatcherTest extends \Psc\Code\Test\Base {
  
  protected $dispatcher;
  
  public function setUp() {
    $this->chainClass = 'Psc\URL\RequestDispatcher';
    parent::setUp();
    $this->dispatcher = new RequestDispatcher();
  }
  
  public function testCanCreateARequest() {
    $request = $this->dispatcher->createRequest('POST', '/my/url', 'mybody', array('Content-Type'=>'text/plain'));
    
    $this->assertInstanceOf('Psc\URL\Request', $request);
    $this->assertEquals('/my/url', $request->getUrl());
    $this->assertEquals('mybody', $request->getData());
    $this->assertEquals('text/plain', $request->getHeaderField('Content-Type'));
  }
  
  public function testCanDispatchARequest() {
    $response = new Response('blubb', new HTTP\Header()); // hachja, das muss halt auch mal schöner
    
    $request = $this->getMock('Psc\URL\Request', array('process','getResponse'), array('/testurl'));
    $request->expects($this->once())->method('process')->will($this->returnValue('blubb'));
    $request->expects($this->atLeastOnce())->method('getResponse')->will($this->returnValue($response));
    
    $response = $this->dispatcher->dispatch(
      $request
    );
    
    $this->assertInstanceOf('Psc\URL\Response', $response);
  }
  
  public function testCanCreatesRequestWithAuthentication() {
    $this->dispatcher->setAuthentication('theuser','itsasecret');
    
    $request = $this->dispatcher->createRequest('POST', '/my/url', 'mybody', array('Content-Type'=>'text/plain'));
    $this->assertEquals('theuser:itsasecret', $request->getOption(CURLOPT_USERPWD));
  }
}
?>