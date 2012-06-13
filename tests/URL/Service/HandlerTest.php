<?php

namespace Psc\URL\Service;

use \Psc\Code\Test\Base,
    \Psc\URL\Service\Handler;

/**
 * @group class:Psc\URL\Service\Handler
 */
class HandlerTest extends \Psc\Code\Test\Base {

  public function testServiceHandling() {
    $handler = new Handler();
    
    /* Register Episodes Service */
    $service = new Service('episodes');
    $service->setAction(function ($name, $identifier = NULL, $sub = NULL) {
      return $name.': '.$identifier.'.'.$sub;
    });
    $handler->register($service);
    
    $this->assertEquals('get: 8.status', $handler->process(new Request(Request::GET, '/episodes/8/status')));
    $this->assertEquals('put: 8.', $handler->process(new Request(Request::PUT, '/episodes/8')));
    $this->assertEquals('index: .', $handler->process(new Request(Request::GET, '/episodes')));
    $this->assertEquals('index: .', $handler->process(new Request(Request::GET, '/episodes/')));
    // usw usf
    
    $service = new Service('lists');
    $service->setAction(function ($method, $identifier) {
      $args = func_get_args();
      $method = array_shift($args);
      $identifier = array_shift($args);
      $subs = $args;
      return compact('method','identifier','subs');
    });
    
    $handler->register($service);
    
    $this->assertEquals(array(
      'method'=>'get',
      'identifier'=>'3',
      'subs'=>array('i','have','no','idea')
      ),
      $handler->process(new Request(Request::GET, '/lists/3/i/have/no/idea'))
    );
    
    $service = $handler->getService();
    $this->assertInstanceOf('Psc\URL\Service\Service',$service);
  }

}

?>