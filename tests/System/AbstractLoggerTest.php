<?php

namespace Psc\System;

use Psc\System\AbstractLogger;

/**
 * @group logger
 */
class AbstractLoggerTest extends \Psc\Code\Test\Base {

  public function testSubloggerListening() {
    
    $subLogger = new BufferLogger();
    $subLogger->setPrefix('[sub]');
    
    $mainLogger = new BufferLogger();
    $mainLogger->setPrefix('[main]');
    
    $mainLogger->listenTo($subLogger);

    $subLogger->writeln('line1');
    $this->assertEquals("[sub] line1\n", $subLogger->toString());
    
    $this->assertEquals("[main] [sub] line1\n",$mainLogger->toString());
  }
  
  public function testDispatchingWrite() {
    $m = $this->getManagerMock('blubb','write');
    
    $logger = new MyLogger($m);
    $logger->write('blubb');
  }

  public function testDispatchingWriteLn() {
    $m = $this->getManagerMock('blubb'."\n",'writeln');
    
    $logger = new MyLogger($m);
    $logger->writeln('blubb');
  }

  public function testDispatchingBr() {
    $m = $this->getManagerMock("\n",'br');
    
    $logger = new MyLogger($m);
    $logger->br();
  }
  
  protected function getManagerMock($msg, $type) {
    $m = $this->getMock('Psc\Code\Event\Manager', array('dispatchEvent'));
    $m->expects($this->once())->method('dispatchEvent')
      ->with($this->equalTo('Logger.Written'), array('msg'=>$msg, 'type'=>$type));
                
    return $m;
  }
}

class MyLogger extends AbstractLogger {
  public function write($msg) {
    $this->dispatch(__FUNCTION__, $msg);
  }

  public function writeln($msg) {
    $this->dispatch(__FUNCTION__, $msg."\n");
  }

  public function br() {
    $this->dispatch(__FUNCTION__, "\n");
  }
  
  public function toString() {
  }
}
?>