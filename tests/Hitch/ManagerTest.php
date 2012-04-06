<?php

namespace Psc\Hitch;

use \Psc\Hitch\Manager;
use \Psc\PSC;

/**
 * @group Hitch
 */
class ManagerTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    PSC::getProject()->getModule('Hitch')->bootstrap()->getProject();
  }

  public function testConstruct() {
    $hitch = new Manager();
  }
}
?>