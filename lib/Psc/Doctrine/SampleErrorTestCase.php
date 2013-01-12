<?php

namespace Psc\Doctrine;

class SampleErrorTestCase extends \Psc\Doctrine\DatabaseTest {
  // configure wird nicht abgeleitet und setzt damit doctrine.connectionName nicht
  
  public function testNothing() {
    // damit phpunit kein warning macht
    $this->assertTrue(true,'Test Runs');
  }
}
?>