<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\UserEntityMeta
 */
class UserEntityMetaTest extends \Psc\Code\Test\Base {
  
  protected $userEntityMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\UserEntityMeta';
    parent::setUp();
    $this->userEntityMeta = new UserEntityMeta('Entitites\User');
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\CMS\EntityMeta', $this->userEntityMeta);
  }
}
?>