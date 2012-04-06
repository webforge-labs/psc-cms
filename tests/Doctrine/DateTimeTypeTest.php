<?php

namespace Psc\Doctrine;

use Psc\Doctrine\DateTimeType;
use Psc\Doctrine\TestEntities\Tag;

class DateTimeTypeTest extends \Psc\Doctrine\DatabaseTest {
  
  public function configure() {
    $this->con = 'tests';
    parent::configure();
  }
  
  public function setUpFixtures() {
    $this->loadEntity('Psc\Doctrine\TestEntities\Tag');
    $this->loadFixtures(array('test_tags'));
    $this->updateEntitySchema('Psc\Doctrine\TestEntities\Tag');
  }
  
  public function assertPreConditions() {
    try {
      $this->hydrate('Psc\Doctrine\TestEntities\Tag',array('label'=>'timestamped'));
    } catch (\Psc\Doctrine\EntityNotFoundException $e) {
      return TRUE;
    }
    
    $this->fail('Tag timestamped ist vorhanden');
  }

  public function testConversion() {
    $tag = new Tag('timestamped');
    
    $this->assertInstanceOf('Psc\DateTime\DateTime',$time = $tag->getCreated());
    
    $this->em->persist($tag);
    $this->em->flush();
    
    $this->em->clear();
    
    $tag = $this->hydrate('Psc\Doctrine\TestEntities\Tag',array('label'=>'timestamped'));
    $this->assertInstanceOf('Psc\DateTime\DateTime', $savedTime = $tag->getCreated());
    $this->assertEquals($time, $savedTime);
  }
}
?>