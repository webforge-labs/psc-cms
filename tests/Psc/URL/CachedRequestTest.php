<?php

namespace Psc\URL;

use \Psc\URL\CachedRequest;

/**
 * @group class:Psc\URL\CachedRequest
 */
class CachedRequestTest extends \Psc\Code\Test\Base {
  
  public function testCached() {
    $req = new CachedRequest('http://www.google.de/');
    $req->setCachedResponse(new Response('ich bin auf jeden Fall nicht Google', new HTTP\Header()));
    
    $this->assertEquals('ich bin auf jeden Fall nicht Google',$req->init()->process());
    $this->assertInstanceOf('Psc\URL\Response',$req->getResponse());
    $this->assertEquals('ich bin auf jeden Fall nicht Google',$req->getResponse()->getRaw());
  }
}

?>