<?php

namespace Psc\Doctrine;

use Psc\Doctrine\Helper as DoctrineHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Psc\PSC;

/**
 * @backupGlobals disabled
 * @group class:Psc\Doctrine\Helper
 */
class HelperTest extends \Psc\Code\Test\Base {
  
  protected $c = '\Psc\Doctrine\Helper';
  
   /**
   * @var Collection
   */
 protected $collection;
  
  /**
   * @var Collectiona
   */
  protected $actualItems;
  
  public function setUp() {
    $this->collection = array();
    $this->collection[] = new HelperCollectionObject(10);
    $this->collection[] = new HelperCollectionObject(20);
    $this->collection[] = new HelperCollectionObject(30);
    $this->collection[] = new HelperCollectionObject(40);
    $this->collection[] = new HelperCollectionObject(50);
    $this->collection[] = new HelperCollectionObject(2);
    $this->collection[] = new HelperCollectionObject(1);
    $this->collection[] = new HelperCollectionObject(3); 


    $this->actualItems = new ArrayCollection();
    $this->actualItems[] = new HelperCollectionObject(10); //20,30 fehlen
    $this->actualItems[] = new HelperCollectionObject(40);
    $this->actualItems[] = new HelperCollectionObject(50);
    $this->actualItems[] = new HelperCollectionObject(2);
    $this->actualItems[] = new HelperCollectionObject(1);
    $this->actualItems[] = new HelperCollectionObject(3);
    $this->actualItems[] = new HelperCollectionObject(99); // 99, 4 sind neu
    $this->actualItems[] = new HelperCollectionObject(4); 
  }
  
  /**
   * expectedException \Psc\System\Exception
   */
  public function testReindex() {
    
    $expected = array();
    foreach ($this->collection as $o) {
      $expected[$o->getId()] = $o;
    }
    
    $reindexed = DoctrineHelper::reindex($this->collection,'getId');
    $this->assertEquals($expected,$reindexed);
    
    /* Referenzen */
    unset($this->collection[0]);
    $this->collection[1]->setId(9999999);
    
    $this->assertNotEquals($expected, $this->collection);
    
    $this->assertEquals($expected,$reindexed);
  }
  
  public function testMergeUnique() {
    $expectedMerge = new ArrayCollection(array(
      new HelperCollectionObject(10),
      new HelperCollectionObject(20),
      new HelperCollectionObject(30),
      new HelperCollectionObject(40),
      new HelperCollectionObject(50),
      new HelperCollectionObject(2),
      new HelperCollectionObject(1),
      new HelperCollectionObject(3),
      new HelperCollectionObject(99),
      new HelperCollectionObject(4)
    ));
                                         
    $mergeItems = DoctrineHelper::mergeUnique($this->collection, $this->actualItems->toArray(), 'Id');
    $this->assertEquals(array_values($expectedMerge->toArray()),array_values($mergeItems));


    $mergeItems = DoctrineHelper::mergeUnique($this->collection, $this->actualItems->toArray(), 'id');
    $this->assertEquals(array_values($expectedMerge->toArray()),array_values($mergeItems));


    $mergeItems = DoctrineHelper::mergeUnique($this->collection, $this->actualItems->toArray(), 'getId');
    $this->assertEquals(array_values($expectedMerge->toArray()),array_values($mergeItems));


    $mergeItems = DoctrineHelper::mergeUnique($this->collection, $this->actualItems->toArray(), function ($e) { return $e->getId(); });
    $this->assertEquals(array_values($expectedMerge->toArray()),array_values($mergeItems));
  }
  
  public function testDeleteDiff() {
    $expectedDelete = new ArrayCollection(array(
      new HelperCollectionObject(20),
      new HelperCollectionObject(30)
    ));
    
    $deleteItems = DoctrineHelper::deleteDiff(new ArrayCollection($this->collection), $this->actualItems);
    
    $this->assertEquals(array_values($expectedDelete->toArray()),array_values($deleteItems->toArray()));
  }

  public function testInsertDiff() {
    $expectedInsert = new ArrayCollection(array(
      new HelperCollectionObject(99),
      new HelperCollectionObject(4)
    ));
    
    $this->assertEquals(array_values($expectedInsert->toArray()),
                        array_values(DoctrineHelper::insertDiff(new ArrayCollection($this->collection), $this->actualItems)->toArray()));
  }
  
  
  public function testEmReCreate() {
    $em = DoctrineHelper::em();
    $em->close();
    
    $this->assertFalse($em->isOpen());
    DoctrineHelper::reCreateEm();
    
    $em = DoctrineHelper::em();
    $this->assertTrue($em->isOpen());
  }
  
  /**
   * @group coll
   */
  public function testCollectionsDebug() {
    $this->assertNotEmpty(DoctrineHelper::debugCollectionDiff($this->collection, $this->actualItems));
  }
  
  public function testDoctrinebug() {
    return 'go on!';
    $query = "SELECT t0.id AS id1, t0.title AS title2, t0.gameTitle AS gameTitle3, t0.abbrevation AS abbrevation4, t0.rbvNumber AS rbvNumber5, t0.oidStart AS oidStart6, t0.oidEnd AS oidEnd7, t0.importDate AS importDate8, t0.postProcessed AS postProcessed9, t0.visible AS visible10, t0.soundNum AS soundNum11 FROM products t0 WHERE t0.id IN (?)";
    
    $types = array(0 => 101);
    $params = array(0 => array());
    
    \Doctrine\DBAL\SQLParserUtils::expandListParameters($query, $params, $types);
  }
}

class HelperCollectionObject {
  
  protected $id;
  
  public function __construct($id) {
    $this->id = $id;
  }
  
  public function getId() {
    return $this->id;
  }

  public function getIdentifier() {
    return $this->id;
  }
  
  public function setId($id) {
    $this->id = $id;
    return $this;
  }
  
  public function equals($otherObject = NULL) {
    if ($otherObject == NULL) return FALSE;
    return $otherObject->getId() === $this->getId();
  }
  
  public function __toString() {
    return 'Object:'.$this->id;
  }
}
?>