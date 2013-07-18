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

  /**
   * @dataProvider provideMatchOrderBy
   */
  public function testMatchOrderBy($orderBy, Array $mappings, $expectedOrderBy) {
    $r = $this->createRequestMatcher(array('find','something'), Service::GET);

    $this->assertEquals(
      $expectedOrderBy,
      $r->matchOrderBy($orderBy, $mappings)
    );
  }
  
  public static function provideMatchOrderBy() {
    $tests = array();

    $defaultMapping = array('name'=>'aliasName', 'type'=>NULL);
  
    $test = function($orderBy, $expectedOrderBy, $mapping = NULL) use (&$tests, $defaultMapping) {
      $tests[] = array($orderBy, $mapping ?: $defaultMapping, $expectedOrderBy);
    };

    // default to empty array
    $test(
      NULL,
      array()
    );

    // default to array as field
    $test(
      'type',
      array('type'=>'ASC')
    );

    $test(
      1,
      array()
    );

    // default to empty if wrong
    $test(
      'nondefined',
      array()
    );

    // uppercase asc
    $test(
      array('type'=>'asc'),
      array('type'=>'ASC')
    );

    // uppercase desc
    $test(
      array('type'=>'desc'),
      array('type'=>'DESC')
    );

    // use ASC as default
    $test(
      array('type'=>NULL),
      array('type'=>'ASC')
    );

    // use ASC as default, if error
    $test(
      array('type'=>'error'),
      array('type'=>'ASC')
    );

    // use alias if specified in mapping
    $test(
      array('name'=>'ASC'),
      array('aliasName'=>'ASC')
    );
  
    return $tests;
  }

  public function createRequestMatcher(Array $parts, $method = Service::GET) {
    return new RequestMatcher(new ServiceRequest($method, $parts));
  }
}
?>