<?php

namespace Psc\CMS;

use Psc\Net\HTTP\Request;

/**
 * @group class:Psc\CMS\RequestMeta
 */
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
  
  public function testUrlRetrieving_addingInput() {
    $this->requestMeta = new RequestMeta(Request::PUT,
                                         '/entities/sounds/%d/%s',
                                         array('PositiveInteger', 'String'),
                                         array(17)
                                        );
    
    $rm1 = clone $this->requestMeta;
    $this->assertEquals('/entities/sounds/17/custom-action', $rm1->getUrl(17, 'custom-action'));

    //$rm2 = clone $this->requestMeta;
    //$this->assertEquals('/entities/sounds/17/custom-action', $rm2->getUrl('custom-action'));
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