<?php

namespace Psc\Net\HTTP;

use Psc\Net\HTTP\RequestHeader;

class RequestHeaderTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Net\HTTP\RequestHeader';
    parent::setUp();
  }

  public function testConstruct() {
    $header = new RequestHeader(RequestHeader::GET, '/blubb.html');
    $this->assertChainable($header->setDefaultValues());
    $this->assertNotNull($header->getField('Content-Type'));
  }
  
  public function testGetStatusLine() {
    $header = new RequestHeader(RequestHeader::GET, '/blubb.html');
    $this->assertEquals('GET /blubb.html HTTP/1.1',$header->getStatusLine());
  }
  
  public function testSendStatusLine() {
    $args = array();
    $testHeader = $this->getMock($this->chainClass,array('sendPHPHeader'),array(RequestHeader::GET, '/blubb.html'));
    
    $testHeader->expects($this->once())
               ->method('sendPHPHeader')
               ->will($this->returnCallback(function ($key, $value, $replace,$code) use (&$args) {
                 $args = func_get_args();
                 return NULL;
               }));
    $testHeader->send();
    $this->assertEquals(array(NULL,$testHeader->getStatusLine(), TRUE,''), $args);
  }

  public function createRequestHeader() {
    return new RequestHeader();
  }
}
?>