<?php

namespace Psc\URL\Service;

use \Psc\Code\Test\Base,
    \Psc\URL\Service\Call;

/**
 * @group class:Psc\URL\Service\Call
 */
class CallTest extends \Psc\Code\Test\Base {
  
  public function testUndefined() {
    $call = new Call('testi',array('eins'));
    $this->assertEquals('eins',$call->getParameter(0));
    $this->assertEquals(Call::UNDEFINED,$call->getParameter(1));
  }
}
