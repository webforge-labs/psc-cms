<?php

namespace Psc\Doctrine;

use \Psc\Doctrine\DatabaseTest;

/* die stubs siehe in psc-cms classes, da hier sonst php unit die tests einfach ausführt */

/**
 * Kein Schreibfehler dass ist der Test für den Database Test!
 */
class DatabaseTestTest extends \Psc\Code\Test\Base {
  
  public function testConfigureProblem() {
    $test = new SampleErrorTestCase('testNothing');
    $test->run();
    
    $this->assertTrue($test->hasFailed());
  }

  public function testFixtureInsert() {
    $em = \Psc\Doctrine\Helper::em();
    $this->assertEquals(\Psc\PSC::getProject()->getLowerName().'_tests', $em->getConnection()->getDatabase());
    $schema = $em->getConnection()->getSchemaManager();
    
    if ($schema->tablesExist(array('users'))) {
      $schema->dropTable('users');
    }
    if ($schema->tablesExist(array('products'))) {
      $schema->dropTable('products');
    }
    
    $this->assertFalse($schema->tablesExist(array('users','products')));
    
    $this->assertTest(new SampleTestCase('testSomething'), TRUE);
    
    $this->assertTrue($schema->tablesExist(array('users','products')));
  }
}
?>