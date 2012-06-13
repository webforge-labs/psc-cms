<?php

namespace Psc\Code\Test;

use Psc\Code\Test\ClosureTestCase;

/**
 * @group class:Psc\Code\Test\ClosureTestCase
 */
class ClosureTestCaseTest extends \Psc\Code\Test\Base {

  public function testInnerAssertion() {
    
    $this->assertTest(
      new ClosureTestCase(
        function ($test) {
          $test->assertEquals('wahr','wahr');
        },
        TRUE
      )
    );
    
  }
}
?>