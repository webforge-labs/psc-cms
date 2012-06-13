<?php

namespace Psc\URL\Service;

use Psc\Code\Test\Base;
use Psc\URL\Service\Service;

/**
 * @group class:Psc\URL\ServiceTest
 */
class ServiceTest extends \Psc\Code\Test\Base {

  public function testAPI() {
    $service = new Service('episodes');
    $service->setController(new MockServiceController());
    $this->assertEquals('episodes',$service->getName());
    
    $this->assertEquals('edit arg1:8',$service->process(new Call('edit', array('arg1'=>8))));
    $this->assertEquals('get arg1:122',$service->process(new Call('get', array('arg1'=>122))));
    
    $service = new Service('zweiter');
    $service->setAction(function ($name,$arg1, $arg2) {
      return $name.'.'.$arg1.':'.$arg2;
    });
    $this->assertEquals('name.eins:zwei', $service->process(new Call('name',array('eins','zwei'))));
  }
}

class MockServiceController implements \Psc\URL\Service\Controller {
  
  public function get($arg1) {
    return 'get arg1:'.$arg1;
  }

  public function edit($arg1) {
    return 'edit arg1:'.$arg1;
  }
}
?>