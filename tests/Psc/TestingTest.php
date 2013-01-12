<?php

namespace Psc;

/**
 * @group experiment
 */
class TestingTest extends \Psc\Code\Test\Base {
  
  protected $wrapperClosure;
  
  public function testObjectCount() {
    $o = new \stdClass;


    $this->assertEquals(0, count(get_object_vars($o)));
  }
  
  public function testFlagsMasking() {
    $nullable = 0x000001;
    $unique   = 0x000002;
    $noDoc    = 0x000004;
    
    $originalFlags = $nullable | $unique | $noDoc;
    
    // thats usual
    $this->assertEquals($nullable, $originalFlags & $nullable);
    $this->assertEquals($unique, $originalFlags & $unique);
    $this->assertEquals($noDoc, $originalFlags & $noDoc);
    
    $maskedFlags = $originalFlags & ($nullable | $unique);
    
    $this->assertEquals($nullable, $maskedFlags & $nullable);
    $this->assertEquals($unique, $maskedFlags & $unique);
    $this->assertEquals(0, $maskedFlags & $noDoc);
  }
  
  public function testClosureScoping() {
    
    $data = array();
    
    $modifying = function () use (&$data) {
      $data[] = TRUE;
    };


    $wrapper = function () use ($modifying) {
      $modifying();
    };
    
    $this->wrapperClosures = array('f'=>$wrapper);
    
    $this->callWrapperClosure();
    $this->assertEquals(array(0=>TRUE), $data);
  }
  
  protected function callWrapperClosure() {
    $this->wrapperClosures['f']();
  }
  
  public function testNumberFormat() {
    $this->assertEquals('1292,25', number_format(1292.25, 2, ',', ''));
  }
}

class ToStringObject {
  
  
  public function __toString() {
    return 'yes im running';
  }
}
?>