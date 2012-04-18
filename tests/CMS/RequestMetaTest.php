<?php

namespace Psc\CMS;

use Psc\Net\HTTP\Request;

class RequestMetaTest extends \Psc\Code\Test\Base {
  
  protected $requestMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\RequestMeta';
    parent::setUp();
  }

  
  public function testUrlRetrieving() {
    $this->requestMeta = new RequestMeta(Request::PUT,
                                         '/entities/sounds/%d/',
                                         array('PositiveInteger')
                                         );
    $this->assertEquals('/entities/sounds/17/', $this->requestMeta->getUrl(17));
  }

  public function testUrlRetrieving_concreteInput() {
    $this->requestMeta = new RequestMeta(Request::PUT,
                                         '/entities/sounds/%d/',
                                         array('PositiveInteger'),
                                         array(17)
                                         );
    $this->assertEquals('/entities/sounds/17/', $this->requestMeta->getUrl());
  }
  
  /**
   * @expectedException Psc\Exception
   */
  public function testUrlRetrieving_MissingParameterThrowsException() {
    $this->requestMeta = new RequestMeta(Request::PUT,
                                         '/entities/sounds/%d/',
                                         array('%d')
                                         );
    
    $this->requestMeta->getUrl();
  }

  /**
   * @expectedException Psc\Exception
   */
  public function testUrlRetrieving_TooFewParameterThrowsException() {
    $this->requestMeta = new RequestMeta(Request::PUT,
                                         '/entities/sounds/%d/%s/',
                                         array('PositiveInteger','String')
                                         );
    
    $this->requestMeta->getUrl(17);
  }
}
?>