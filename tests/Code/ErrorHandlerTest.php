<?php

namespace Psc\Code;

use Psc\Code\ErrorHandler;
use Psc\Code\Code;
use Psc\PSC;
use Psc\Code\Callback;

/**
 * @group class:Psc\Code\ErrorHandler
 */
class ErrorHandlerTest extends \Psc\Code\Test\Base {
  
  protected $c = '\Psc\Code\ErrorHandler';
  
  protected $instance;
  
  public function testErrorHandler() {
    $rec = 'p.scheit@ps-webforge.com';
    
    $eh = new ErrorHandler();
    
    $this->assertInstanceOf($this->c,$eh->setRecipient($rec));
    $this->assertEquals($rec,$eh->getRecipient());
    
    $eh->setRecipient(NULL); // denn mail können wir lokal nicht
    
    // wir "faken" einen Error
    //trigger_error('Hier ist ein FakeFehler', E_USER_ERROR);
    
    // dies ist der var export wenn man das oben ausführt
    $errorParams = 
  array (
  0 => 256,
  1 => 'Hier ist ein FakeFehler',
  2 => 'D:\\www\\psc-cms\\Umsetzung\\base\\src\\psc\\tests\\Code\\ErrorHandlerTest.php',
  3 => 23,
  4 =>
  array (
    'rec' => 'p.scheit@ps-webforge.com',
    'eh' =>$eh
    )
  );
  
  // hier der Fake
    $cb = new Callback($eh,'handle');
    
    try {
      $cb->call($errorParams);
    } catch (\ErrorException $e) {
      $this->assertEquals(256, $e->getSeverity());
      $this->assertEquals(0,$e->getCode());
      $this->assertEquals('Hier ist ein FakeFehler',$e->getMessage());
    }
    
    // wie asserten wir hier, dass error_log() was geschrieben hat?
  }
  
  public function testRegister() {
    $this->assertInstanceOf($this->c,PSC::registerErrorHandler());
    
    $eh = PSC::getEnvironment()->getErrorHandler();
    $this->assertEquals(\Psc\Config::get('debug.errorRecipient.mail'),$eh->getRecipient()); // denn das steht in der Config
    
    PSC::unregisterErrorHandler();
  }
  
}

?>