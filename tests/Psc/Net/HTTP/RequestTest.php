<?php

namespace Psc\Net\HTTP;

use \Psc\Net\HTTP\Request;

/**
 * @group net-service
 * @group class:Psc\Net\HTTP\Request
 */
class RequestTest extends \Psc\Code\Test\Base {

  public function testApi() {
    $request = new Request(Request::GET, '/episodes/8/status');
    
    $this->assertEquals('episodes',$request->part(1));
    $this->assertEquals('8',$request->part(2));
    $this->assertEquals('status',$request->part(3));
    $this->assertEquals(NULL,$request->part(4));
    
    $this->assertEquals(Request::GET,$request->getMethod());
  }
  
  /**
   * @expectedException \InvalidArgumentException
   */
  public function testPart0Exception() {
    $request = new Request(Request::GET, '/episodes/8/status');
    $request->part(0);
  }

  /**
   * @expectedException \Psc\Exception
   */
  public function testTypeException() {
    $request = new Request('blubb', '/episodes/8/status');
  }
  
  public function testTypes() {
    $request = new Request(Request::GET,'/service/identifier/sub');
    $request = new Request(Request::POST,'/service/identifier/sub');
    $request = new Request(Request::PUT,'/service/identifier/sub');
    $request = new Request(Request::DELETE,'/service/identifier/sub');
  }
  
  public function testAccepting() {
    $jsonRequest = Request::create(Request::GET, '/', NULL, array('Accept'=>'application/json, text/javascript, */*; q=0.01'));
    $this->assertTrue($jsonRequest->accepts('application/json'), 'accept field is: '.$jsonRequest->getHeaderField('accept'));
    $this->assertFalse($jsonRequest->accepts('text/html'));
    
    $htmlRequest = Request::create(Request::GET, '/', NULL, array('Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'));
    $this->assertTrue($htmlRequest->accepts('text/html'));
    $this->assertFalse($htmlRequest->accepts('application/json'));
  }
}
