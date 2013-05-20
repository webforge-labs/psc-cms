<?php

namespace Psc\Doctrine;

use Psc\Doctrine\DateTimeType;
use Psc\Doctrine\TestEntities\Tag;

/**
 * @group class:Psc\Doctrine\DateTimeType
 */
class DateTimeTypeTest extends \Psc\Doctrine\DatabaseTestCase {
  
  public function setUp() {
    $this->fixtures = new \Psc\Doctrine\TestEntities\TagsFixture;
    parent::setUp();
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
    
    $this->assertInstanceOf('Webforge\Common\DateTime\DateTime',$time = $tag->getCreated());
    
    $this->em->persist($tag);
    $this->em->flush();
    
    $this->em->clear();
    
    $tag = $this->hydrate('Psc\Doctrine\TestEntities\Tag',array('label'=>'timestamped'));
    $this->assertInstanceOf('Webforge\Common\DateTime\DateTime', $savedTime = $tag->getCreated());
    $this->assertEquals($time, $savedTime);

  }
}
?>