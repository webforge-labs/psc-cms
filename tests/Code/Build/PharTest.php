<?php

namespace Psc\Code\Build;

use \Psc\Code\Build\Phar;

/**
 * @group class:Psc\Code\Build\Phar
 */
class PharTest extends \Psc\Code\Test\Base {

  public function testConstruct_setsUnderscoreStyle() {
    $phar = new Phar($this->newFile('out.php'),
                     $this->getTestDirectory(),
                     'PHPWord_'
                     );
    
    $this->assertTrue($phar->getUnderscoreStyle());
  }
}

?>