<?php

namespace Psc;

/**
 * @group experiment
 */
class TestingTest extends \Psc\Code\Test\Base {
  
  protected $wrapperClosure;
  
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