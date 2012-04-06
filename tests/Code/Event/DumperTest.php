<?php

namespace Psc\Code\Event;

use \Psc\Code\Event\Dumper;

class DumperTest extends \Psc\Code\Test\Base {
  
  public function testAcceptance() {
    $manager = new \Psc\Code\Event\Manager();
    
    $testDispatcher = $this->getMock('Psc\Code\Event\Dispatcher', array('getManager'));
    $testDispatcher->expects($this->any())
                  ->method('getManager')
                  ->will($this->returnValue($manager));
    
    $dumper = new Dumper($testDispatcher);
    $this->assertEquals(0, $dumper->getCount());
    
    $e1 = $manager->dispatchEvent('Psc.test', $this);
    $this->assertEquals(1, $dumper->getCount());
    
    $e2 = $manager->dispatchEvent('Psc.test', $this);
    $this->assertEquals(2, $dumper->getCount());
    $this->assertEquals(array($e1,$e2), $dumper->getDumps());
    
    $dumper->reset();
    $this->assertEquals(0,$dumper->getCount());
    $this->assertEquals(array(),$dumper->getDumps());
  }
}

?>