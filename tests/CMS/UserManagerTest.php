<?php

namespace Psc\CMS;

/**
 * Dependency Package für Klassen die Users aus der Datenbank laden müssen
 *
 * (siehe z.B. \Psc\CMS\Auth)
 */
class UserManagerTest extends \Psc\Code\Test\Base {
  
  protected $userManager;
  protected $entityRepository;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\UserManager';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $user = new \Entities\User();
    $user->setEmail($id = 'p.scheit@ps-webforge.com');
    
    $this->entityRepository = $this->doublesManager->buildEntityRepository('Entities\User')
      ->expectHydrates($user,$this->once())
      ->build();
    
    $this->userManager = new UserManager($this->entityRepository);
    $this->assertSame($user, $this->userManager->get($id));
  }
  
  public function testGetThrowsNoUserFoundException() {
    $this->entityRepository = $this->doublesManager->buildEntityRepository('Entities\User')
      //->expectHydrates($user,$this->once())
      ->build();
    
    $this->userManager = new UserManager($this->entityRepository);
  }
}
?>