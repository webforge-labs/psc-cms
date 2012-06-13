<?php

namespace Psc\Doctrine;

use Psc\Doctrine\Hydrator;

/**
 * @group class:Psc\Doctrine\Hydrator
 */
class HydratorTest extends \Psc\Doctrine\DatabaseTest {
  
  protected $hydrator;

  public function configure() {
    $this->con = 'tests';
    parent::configure();
  }
  
  public function setUp() {
    parent::setUp();

    $this->loadEntity('Tag');
    if (!$this->tableExists('test_tags'))
      $this->updateEntitySchema('Tag');
    
    $this->hydrator = new Hydrator($this->getEntityName('Tag'), $this->em);
  }
  
  /**
   *
   *
   * nicht das common Directory benutzen sondern unsers
   */
  public function getDBDirectory() {
    return $this->getTestDirectory()->sub('db/')->create();
  }
  
  public function getEntityName($shortName) {
    return 'Psc\Doctrine\TestEntities\\'.$shortName;
  }
  
  /**
   * @dataProvider provideListHydration
   */
  public function testListHydration(Array $list, Array $expectedIdentifiers) {
    $tags = $this->hydrator->byList($list, 'label');
    
    $identifiers = array();
    foreach ($tags as $tag) {
      $this->assertGreaterThan(0,$tag->getIdentifier());
      $this->assertInstanceof($this->getEntityName('Tag'), $tag);
      
      $identifiers[] = $tag->getIdentifier();
    }
    
    $this->assertEquals($expectedIdentifiers, $identifiers, 'Listen sind nicht gleich', 0, 1, TRUE); //regardless of order
  }
  
  public function provideListHydration() {
    $tests[0] = array(
      array('hot','spicy','artifical'),
      array(1,2,3)
    );
    
    $tests[0] = array(
      array(),
      array()
    );

   return $tests;
  }
}

?>