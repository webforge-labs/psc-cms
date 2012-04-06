<?php

use \Psc\DateTime\TimeBenchmark;

class TimeBenchmarkTest extends PHPUnit_Framework_TestCase {

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