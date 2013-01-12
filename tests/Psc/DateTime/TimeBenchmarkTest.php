<?php

namespace Psc\DateTime;

use \Psc\DateTime\TimeBenchmark;

/**
 * @group class:Psc\DateTime\TimeBenchmark
 */
class TimeBenchmarkTest extends \Psc\Code\Test\Base {

    public function testFactorOutput() {
      $bench = new TimeBenchmark();
      sleep(1);
      $bench->end();
      
      $this->assertGreaterThanOrEqual( 1, $bench->getTime(TimeBenchmark::SECONDS));
      $this->assertGreaterThanOrEqual( 1000, $bench->getTime(TimeBenchmark::MILLISECONDS));
      $this->assertGreaterThanOrEqual( 1000000, $bench->getTime(TimeBenchmark::MICROSECONDS));
    }
}
?>