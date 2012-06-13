<?php

namespace Psc\Doctrine;

/**
 * @group class:Psc\Doctrine\ProjectFixture
 */
class ProjectFixtureTest extends \Psc\Code\Test\Base {
  
  protected $projectFixture;
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\ProjectFixture';
    parent::setUp();
    //$this->projectFixture = new ProjectFixture();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>