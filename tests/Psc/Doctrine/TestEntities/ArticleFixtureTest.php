<?php

namespace Psc\Doctrine\TestEntities;

/**
 * @group class:Psc\Doctrine\TestEntities\ArticleFixture
 */
class ArticleFixtureTest extends \Psc\Code\Test\Base {
  
  protected $articleFixture;
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\TestEntities\ArticleFixture';
    parent::setUp();
    //$this->articleFixture = new ArticleFixture();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>