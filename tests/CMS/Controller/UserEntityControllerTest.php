<?php

namespace Psc\CMS\Controller;

/**
 * @group class:Psc\CMS\Controller\UserEntityController
 */
class UserEntityControllerTest extends \Psc\Code\Test\Base {
  
  protected $ctrl;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\UserEntityController';
    parent::setUp();
    $this->ctrl = new UserEntityController();
  }
  
  public function testAcceptance() {
    $this->assertEquals('Entities\User', $this->ctrl->getEntityName());
  }
}
?>