<?php

namespace Psc\CMS;

use Entities\User;
use Psc\Code\Test\Mock\SessionMock;
use Psc\Code\Test\Mock\CookieManagerMock;

class AuthTest extends \Psc\Code\Test\Base {
  
  protected $userManager;
  protected $session;
  protected $cookies;
  protected $auth;
  
  protected $user;
  protected $id;
  protected $pw;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Auth';
    parent::setUp();
    $this->session = new SessionMock();
    $this->cookies = new CookieManagerMock();
    $this->userManager = $this->getMock('Psc\CMS\UserManager', array('get'), array(),'',FALSE);
    $this->auth = new Auth($this->session, $this->cookies, $this->userManager);
    
    $this->user = new User();
    $this->user->setEmail($this->id = 'p.scheit@ps-webforge.com');
    $this->user->hashPassword($this->pw = 'mySecretPW');
  }
  
  public function testAfterLoginValidateIsOkay() {
    // login
    //print md5($pw);
    $this->auth->login($this->id,$this->pw);
    
    // userManager retrieves mit der Email aus der Datenbank
    // atLeastOnce damit wir das auch in logout benutzen können
    $this->expectUserInDB();
    
    $this->auth->validate();
    
    // auth sets the user correctly
    $this->assertSame($this->user, $this->auth->getUser());
  }
  
  protected function expectUserInDB() {
    $this->userManager->expects($this->once())->method('get')->with($this->equalTo($this->id))->will($this->returnValue($this->user));
  }
  
  public function testAfterLogoutValidateIsNotOkay() {
    // pre: validate does not throw exception
    $this->auth->login($this->id, $this->pw);
    $this->expectUserInDB();
    $this->auth->validate();
    
    // now logout
    $this->auth->logout();
    
    try {
      $this->auth->validate();
      
      $this->fail('validate sollte eine exception schmeissen, da der User ausgeloggt ist');
    } catch (NoAuthException $e) {
      return;
    }
    
    $this->fail('Falsche Exception Gecatched: '.\Psc\Code\Code::getClass($e));
  }
}
?>