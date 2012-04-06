<?php

namespace Psc\System;

use \Psc\System\EchoLogger;

/**
 * @group logger
 */
class EchoLoggerTest extends \Psc\Code\Test\Base {

  public function testOutputtingAcceptance() {
    $this->expectOutputString('this log is'."\n".'break up into two lines');
    
    $log = new EchoLogger();
    $log->writeln('this log is');
    $log->write('break up into two lines');
  }
}
?>