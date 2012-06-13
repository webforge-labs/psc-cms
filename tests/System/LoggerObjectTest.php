<?php

namespace Psc\System;

/**
 * @group class:Psc\System\LoggerObject
 */
class LoggerObjectTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\System\LoggerObject';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $o = new LoggerObject();
    // nimmt Prefix Dynamisch vom Klassenname
    $o->setLogger(new BufferLogger());
    
    $o->logf('line%d',1);
    $o->log('line2');
    
    // assert string
    $this->assertEquals('[LoggerObject] line1'."\n".'[LoggerObject] line2'."\n", $o->getLogger()->toString());
  }
}
?>