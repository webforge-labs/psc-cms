<?php

namespace Psc\URL;

use \Psc\URL\Request;

class RequestTest extends \Psc\Code\Test\Base {
  
  public function testResponseCreation() {
    /* mhhh das ist schon so so ein test, wo ich keine ahnung habe, wie ich das wirklich testen soll
       
       ich kann uwar hier nochmal curl als driver abstrahieren ode rso, aber das hilft mir auch nicht weiter
       irgendwann muss ich die curl aufrufe testen
    */
    $this->markTestSkipped('test ist nicht unabhängig von der Internet-Connection');
    
    $request = new Request('http://www.google.de/');
    $request->init()->process();
    $response = $request->getResponse();

    $this->assertNotEmpty($response->getRaw());
    $this->assertEquals('text/html; charset=utf-8', $response->getHeader()->getField('Content-Type'));
    $this->assertEquals('1.1', $response->getHeader()->getVersion());
    $this->assertEquals(200, $response->getHeader()->getCode());
    $this->assertEquals('OK', $response->getHeader()->getReason());
    $this->assertInstanceOf('Psc\DateTime\DateTime',$response->getDate());
  }
  
  public function testPostWithInnerArrays() {
    $request = new Request('http://www.google.de');
    
    // das gibt dne error (array to string conversion) weil er versucht den inneren in einen string zu konvertieren
    $request->setPost(array('values'=>array('one','tw','three')));
    $request->init(); // würde error schmeissen, sonst
  }
}

?>