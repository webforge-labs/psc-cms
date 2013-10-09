<?php

namespace Psc\Doctrine;

use Psc\Doctrine\Hydrator;

class HydratorTest extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $hydrator;

  public function setUp() {
    parent::setUp();
    
    $this->tagName = 'Psc\Doctrine\TestEntities\Tag';
    $this->hydrator = new Hydrator($this->tagName, $this->dc);
  }
  
  /**
   * @dataProvider provideListHydration
   */
  public function testListHydration(Array $list, Array $expectedIdentifiers) {
    $tags = $this->hydrator->byList($list, 'label');
    
    $identifiers = array();
    foreach ($tags as $tag) {
      $this->assertGreaterThan(0,$tag->getIdentifier());
      $this->assertInstanceof($this->tagName, $tag);
      
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
