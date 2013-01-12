<?php

namespace Psc\URL;

use \Psc\URL\Response;

/**
 * @group class:Psc\URL\Response
 */
class ResponseTest extends \Psc\Code\Test\Base {
  
  public function testConstruct() {
    
    new Response('empty', new HTTP\Header());
    
  }
}
?>