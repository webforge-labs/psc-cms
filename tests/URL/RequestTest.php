<?php

namespace Psc\URL;

use \Psc\URL\Request;

/**
 * @group class:Psc\URL\Request
 */
class RequestTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    parent::setUp();
    $this->hostConfig = \Psc\PSC::getProjectsFactory()->getHostConfig();
  }
}

?>