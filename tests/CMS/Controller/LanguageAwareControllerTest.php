<?php

namespace Psc\CMS\Controller;

/**
 * @group class:Psc\CMS\Controller\LanguageAwareController
 */
class LanguageAwareControllerTest extends \Psc\Code\Test\Base {
  
  protected $languageAwareController;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\LanguageAwareController';
    parent::setUp();
    //$this->languageAwareController = new LanguageAwareController();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>