<?php

namespace Psc\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * @group class:Psc\Doctrine\FixturesManager
 */
class FixturesManagerTest extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $fixturesManager;
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\FixturesManager';
    parent::setUp();
    
    $this->fixturesManager = new FixturesManager($this->em);
  }
  
  public function testExecuteLoadsAddedFixtures() {
    $myFixture = $this->createFixture();
    $myFixture2 = $this->createFixture2();
    
    $myFixture->expects($this->once())->method('load');
    $myFixture->expects($this->once())->method('load');
  
    $this->fixturesManager->add($myFixture);
    $this->fixturesManager->add($myFixture2);
    $this->fixturesManager->execute();
  }
  
  protected function createFixture($matcher = NULL) {
    $myFixture = $this->getMock(__NAMESPACE__.'\\MyFixture',array('load'));
    return $myFixture;
  }

  protected function createFixture2($matcher = NULL) {
    $myFixture = $this->getMock(__NAMESPACE__.'\\MyFixture2',array('load'));
    return $myFixture;
  }
}

// Jede Fixture Klasse (nach name) geht nur einmal
class MyFixture extends \Psc\Doctrine\Fixture {
  
  public function load(ObjectManager $manager) {}
}

class MyFixture2 extends \Psc\Doctrine\Fixture {
  
  public function load(ObjectManager $manager) {}
}
?>