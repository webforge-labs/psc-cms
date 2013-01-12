<?php

namespace Psc\CMS;

/**
 * Dependency Package für Klassen die Users aus der Datenbank laden müssen
 *
 * (siehe z.B. \Psc\CMS\Auth)
 * @group class:Psc\CMS\UserManager
 */
class UserManagerTest extends \Psc\Code\Test\Base {
  
  protected $userManager;
  protected $entityRepository;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\UserManager';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $user = new \Psc\Entities\User();
    $user->setEmail($id = 'p.scheit@ps-webforge.com');
    
    $this->entityRepository = $this->doublesManager->buildEntityRepository('Psc\Entities\User')
      ->expectHydrates($user,$this->once())
      ->build();
    
    $this->userManager = new UserManager($this->entityRepository);
    $this->assertSame($user, $this->userManager->get($id));
  }
  
  /**
   * @expectedException Psc\CMS\NoUserException
   */
  public function testGetThrowsNoUserFoundException() {
    $this->entityRepository = $this->doublesManager->buildEntityRepository('Psc\Entities\User')
      ->expectDoesNotFind('p.sch@blubb.de',$this->once())
      ->build();
    
    $this->userManager = new UserManager($this->entityRepository);
    $this->userManager->get('p.sch@blubb.de');
  }
}
?>