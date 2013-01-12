<?php

namespace Psc\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;

class ProjectFixture extends Fixture {
  
  protected $project;
  
  public function __construct(\Psc\CMS\Project $project = NULL) {
    $this->project = $project ?: \Psc\PSC::getProject();
  }
  
  /**
   * Load data fixtures with the passed EntityManager
   *
   * flush in ableitender Klasse machen!
   * @param Doctrine\Common\Persistence\ObjectManager $manager
   */
  public function load(ObjectManager $manager) {
    $c = $this->project->getUserClass();
		
    $user = new $c('p.scheit@ps-webforge.com');
    $user->setPassword('583cdd008f2ea237bfe4d39a2d827f42');
    $manager->persist($user);
	
    $user = new $c('psc-laptop@ps-webforge.com');
    $user->setPassword('9606fe7ecf5e4e76e4fa0f07c97e3e49');
    $manager->persist($user);
  }
}
?>