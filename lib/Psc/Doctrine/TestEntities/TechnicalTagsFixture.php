<?php

namespace Psc\Doctrine\TestEntities;

use Doctrine\Common\Persistence\ObjectManager;

class TechnicalTagsFixture extends \Psc\Doctrine\Fixture {
  
  /**
   * Load data fixtures with the passed EntityManager
   * 
   * @param Doctrine\Common\Persistence\ObjectManager $manager
   */
  public function load(ObjectManager $manager) {
    // constraints: DropBox2ValidatorRuleTest geht davon aus, dass nicht mehr als 22 Tags hier drin sind
    
    $tags = array();
    $tags['t1'] = new Tag('migration');
    $tags['t2'] = new Tag('integration');
    $tags['t3'] = new Tag('php');
    $tags['t4'] = new Tag('audio');
    $tags['t5'] = new Tag('favorite');
    $tags['t6'] = new Tag('locked');
    
    foreach ($tags as $tag) {
      $manager->persist($tag);
    }
    
    $manager->flush();
  }
  
  public function getTagsSize() {
    return 5;
  }
}
?>