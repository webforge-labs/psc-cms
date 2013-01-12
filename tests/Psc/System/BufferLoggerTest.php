<?php

namespace Psc\System;

use \Psc\System\BufferLogger;

/**
 * @group logger
 * @group class:Psc\System\BufferLogger
 */
class BufferLoggerTest extends \Psc\Code\Test\LoggerTest {
  
  public function setUp() {
    $this->logger = new BufferLogger();
  }

  public function testWork() {
    $eol = "\n";
    $this->logger->writeln("line1");
    $this->logger->writeln("line2");
    
    $this->assertEquals('line1'.$eol.'line2'.$eol, $this->logger->toString());
    
    $this->logger->write('appended2');
    $this->assertEquals('line1'.$eol.'line2'.$eol.'appended2', $this->logger->toString());
    
    $this->assertInstanceOf('Psc\System\BufferLogger', $this->logger->reset(), 'reset chainable');
    $this->assertEquals('', $this->logger->toString());
  }
  
  public function testPrefix() {
    $this->logger->setPrefix('[prefix]');
    $this->logger->writeln('line1');
    
    $this->assertEquals("[prefix] line1\n", $this->logger->toString());

    $this->logger->setPrefix('[otherPrefix]');
    $this->logger->writeln('line2');
    $this->assertEquals("[prefix] line1\n[otherPrefix] line2\n", $this->logger->toString());
  }
}
?>