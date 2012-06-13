<?php

namespace Psc;

use Psc\Exception;

/**
 * @group class:Psc\Exception
 */
class ExceptionTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Exception';
    parent::setUp();
  }

  public function testCreate() {
    $prev = $this->createException('Ich bin die Exception davor');
    
    $ex = Exception::create('Es wurden %d ints und bla als "%s" gefunden', 17, "blubb")
        ->setCode(7);
    
    $this->assertEquals('Es wurden 17 ints und bla als "blubb" gefunden',$ex->getMessage());
    $this->assertEquals(7,$ex->getCode());
  }
  
  /**
   * @expectedException \Psc\Exception
   */
  public function testSetPreviousNotAllowed() {
    Exception::create('nix')
      ->setPrevious(new \Exception('leer'));
  }

  public function testBuild() {
    $prev = $this->createException('Ich bin die Exception davor');
    
    $ex = Exception::build('Es wurden %d ints und bla als "%s" gefunden', 17, "blubb")
        ->setCode(7)
        ->setPrevious(new \Exception('leer'))
        ->end();
    
    $this->assertEquals('Es wurden 17 ints und bla als "blubb" gefunden',$ex->getMessage());
    $this->assertEquals(7,$ex->getCode());
  }
  
  public function testSetMessage() {
    $ex = $this->createException('leer');
    $this->assertEquals('leer',$ex->getMessage());
    $this->assertChainable($ex->setMessage('voll'));
    $this->assertEquals('voll',$ex->getMessage());
  }
  
  public function testGetClass() {
    $std = new \Psc\Exception('Standard');
    $this->assertEquals('Psc\Exception',$std->getClass());
    
    $special = new MySpecialException('spec');
    $this->assertEquals('Psc\MySpecialException',$special->getClass());
  }
  
  public function testHandlerPrintsTextAndDelegatesToErrorHandler() {
    $handlerMock = $this->getMock('Psc\Code\ErrorHandler',array('handleCaughtException'));
    
    $handlerMock->expects($this->once())
                ->method('handleCaughtException')
                ->will($this->returnSelf());
    
    PSC::getEnvironment()->setErrorHandler($handlerMock);
    
    $this->expectOutputRegex('/does not matter/');
    Exception::handler(new Exception('does not matter'));
  }
  
  public function testExceptionText_defaultIsHTML() {
    $ex = $this->createFullException();
    $html = Exception::getExceptionText($ex, 'html');
    $def = Exception::getExceptionText($ex);
    
    $this->assertEquals($html,$def);
  }
  
  public function testExceptionText_textHasNoTags() {
    $text = Exception::getExceptionText($this->createFullException(), 'text');
    $strip = strip_tags($text);
    $this->assertEquals($strip, $text);
  }
  
  public function testExceptionText_replacesPathsWithProject() {
    $text = Exception::getExceptionText($this->createFullException(),'text', PSC::getProject());
    
    $this->assertContains('{SRC_PATH}',$text);
  }

  public function testExceptionText_containsExceptionName() {
    $ex = new MySpecialException();
    $text = Exception::getExceptionText($ex, 'text');
    
    $this->assertContains('Psc\MySpecialException',$text);
    $this->assertNotContains('Previous Exception',$text);

    $html = Exception::getExceptionText($ex, 'html');
    
    $this->assertContains('Psc\MySpecialException',$html);
    $this->assertNotContains('Previous Exception',$html);
  }
  
  public function testExceptionText_noticesPreviousException() {
    $text = Exception::getExceptionText($this->createFullException(), 'text');
    $this->assertContains('Previous Exception',$text);

    $html = Exception::getExceptionText($this->createFullException(), 'html');
    $this->assertContains('Previous Exception',$html);
  }
  
  public function createException($msg) {
    return new Exception($msg);
  }
  
  public function createFullException() {
    return new Exception('I have everything', 200, new Exception('I was previous',201));
  }
}

class MySpecialException extends \Psc\Exception {
  
  public $info;
}
?>