<?php

namespace Psc\Net;

/**
 * @group class:Psc\Net\RequestMatcher
 */
class RequestMatcherTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Net\RequestMatcher';
    parent::setUp();
  }
  
  public function testMatchValue() {
    $this->assertEquals('products', $this->createRequestMatcher(array('products'))->matchValue('products'));
  }

  /**
   * @expectedException Psc\Net\RequestMatchingException
   */
  public function testDoesntMatchValue() {
    $this->createRequestMatcher(array('products'))->matchValue('wurst');
  }


  public function testMatchIValue() {
    $this->assertEquals('products', $this->createRequestMatcher(array('Products'))->matchIValue('products'));
  }

  /**
   * @expectedException Psc\Net\RequestMatchingException
   */
  public function testDoesntMatchIValue() {
    $this->assertEquals('products', $this->createRequestMatcher(array('Product'))->matchIValue('products'));
  }
  

  public function testMatchRX() {
    $this->assertEquals(array('products','cts'), $this->createRequestMatcher(array('products'))->matchRx('/^produ(cts)$/'));
  }

  /**
   * @expectedException Psc\Net\RequestMatchingException
   */
  public function testDoesntMatchRX() {
    $this->createRequestMatcher(array('sounds'))->matchRx('/\d+/');
  }
  
  public function testMatchId() {
    $this->assertEquals(7, $this->createRequestMatcher(array('7'))->matchId());
    $this->assertEquals(777, $this->createRequestMatcher(array('777'))->matchId());
  }
  
  public function testQMatch() {
    $this->assertEquals('in', $this->createRequestMatcher(array('acomplexvalueinrx'))->qmatchRx('/acom(plex)value(in)rx/',2));
  }
  
  /**
   * @expectedException Psc\Net\RequestMatchingException
   * @dataProvider provideDoesntMatchId
   */
  public function testDoesntMatchId($part) {
    $this->createRequestMatcher(array($part))->matchId();
  }
  
  public static function provideDoesntMatchId() {
    return Array(
      array('blubb'),
      array('0'),
      array(0),
      array('07blubb')
    );
  }

  public function createRequestMatcher(Array $parts, $method = Service::GET) {
    return new RequestMatcher(new ServiceRequest($method, $parts));
  }
}
?>