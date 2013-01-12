<?php

namespace Psc\CMS\Controller;

require_once __DIR__.DIRECTORY_SEPARATOR.'ControllerBaseTest.php';

use Psc\Entities\User;

class UserEntityControllerTest extends ControllerBaseTest {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\UserEntityController';
    $this->entityFQN = 'Psc\Entities\User';
    parent::setUp();
  }

  public function testSelfTestEntityName() {
    $this->assertEquals($this->entityFQN, $this->controller->getEntityName());
  }
  
  public function testPasswordGetsHashedWhenUserIsCreatedOrUpdated() {
    $newUser = new User('info@ps-webforge.com');
    $newUser->hashPassword('test1');
    
    $this->expectRepositorySavesEqualTo($newUser);
    
    $data = (object) array(
      'email'=>'info@ps-webforge.com',
      'password'=>array(
        'password'=>'test1',
        'confirmation'=>'test1'
      )
    );
    
    $user = $this->controller->insertEntity($data);
  }
}
?>