<?php

namespace Psc\Code\Test;

class LoggerTest extends \Psc\Code\Test\Base {
  
  protected $logger;
  
  public function assertPreConditions() {
    $this->assertInstanceOf('Psc\System\Logger',$this->logger, 'Bitte den Logger in setUp() setzen');
  }
  
  public function testAPI() {
    $this->assertInstanceOf('Psc\System\Logger',$this->logger->write('Hi ich bin der erste Eintrag im Logger'),'chainable write');
    $this->assertInstanceOf('Psc\System\Logger',$this->logger->br(),'chainable br');
    $this->assertInstanceOf('Psc\System\Logger',$this->logger->writeln('Hi ich bin der zweite Eintrag im Logger'),'chainable writeln');
    
    $this->assertNotEmpty($this->logger->toString());
  }
}
?>