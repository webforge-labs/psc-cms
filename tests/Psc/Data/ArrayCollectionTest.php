<?php

namespace Psc\Data;

use \Psc\Data\ArrayCollection;

/**
 * @group class:Psc\Data\ArrayCollection
 */
class ArrayCollectionTest extends \Psc\Code\Test\Base {
  
  protected $collection;
  
  public function setUp() {
    parent::setUp();
    
    $this->collection = new ArrayCollection(array(1,2,4,3));
  }

  public function testJSONExport() {
    $this->assertEquals(json_encode(array(1,2,4,3)), $this->collection->JSON());
  }
  
  public function testSort() {
    $this->collection->sortBy(function ($a, $b) {
      return strcmp($a,$b);
    });
    
    $this->assertEquals(array(1,2,3,4),
                        $this->collection->toArray());
  }
  
  

  public function testEntityCollectionSameandEquals() {
    $collection1 = new ArrayCollection();
    $collection1[] = new CollectionObject(10);
    $collection1[] = new CollectionObject(20);
    $collection1[] = new CollectionObject(30);
    $collection1[] = new CollectionObject(40);
    $collection1[] = new CollectionObject(50);
    $collection1[] = new CollectionObject(2);
    $collection1[] = new CollectionObject(1);
    $collection1[] = new CollectionObject(3); 

    $collection2 = new ArrayCollection();
    $collection2[] = new CollectionObject(10);
    $collection2[] = new CollectionObject(30); // vertauscht
    $collection2[] = new CollectionObject(20); // vertauscht
    $collection2[] = new CollectionObject(40);
    $collection2[] = new CollectionObject(50);
    $collection2[] = new CollectionObject(2);
    $collection2[] = new CollectionObject(1);
    $collection2[] = new CollectionObject(3);

    // selbe collection ist natürlich same
    $this->assertTrue($collection1->isSame($collection1, ArrayCollection::COMPARE_COMPARABLE));
    $this->assertTrue($collection2->isSame($collection2, ArrayCollection::COMPARE_COMPARABLE));

    // mit compareTo() sind diese collections nicht gleich, weil vertauscht
    $this->assertFalse($collection1->isSame($collection2, ArrayCollection::COMPARE_COMPARABLE));
    $this->assertFalse($collection2->isSame($collection1,  ArrayCollection::COMPARE_COMPARABLE));
    
    // mit compareTo() sind diese collections equal, obwohl vertauscht
    $this->assertTrue($collection1->isEqual($collection2, ArrayCollection::COMPARE_COMPARABLE));
    $this->assertTrue($collection2->isEqual($collection1, ArrayCollection::COMPARE_COMPARABLE));
    
    // mit object_hash (false ist klar, da wir über all new CollectionObject() machen 
    $this->assertFalse($collection1->isEqual($collection2, ArrayCollection::COMPARE_OBJECTS));

    $collection2 = new ArrayCollection();
    $collection2[] = new CollectionObject(10);
    $collection2[] = new CollectionObject(20);
    $collection2[] = new CollectionObject(30);
    $collection2[] = new CollectionObject(40);
    $collection2[] = new CollectionObject(50);
    $collection2[] = new CollectionObject(2);
    $collection2[] = new CollectionObject(1);
    $collection2[] = new CollectionObject(3);
    
    $this->assertTrue($collection1->isSame($collection2, ArrayCollection::COMPARE_COMPARABLE));
    $this->assertTrue($collection2->isSame($collection1,  ArrayCollection::COMPARE_COMPARABLE));
    $this->assertTrue($collection1->isEqual($collection2, ArrayCollection::COMPARE_COMPARABLE));
    $this->assertTrue($collection2->isEqual($collection1, ArrayCollection::COMPARE_COMPARABLE));
  }
  
  
  public function testCollectionDiff() {
    $collection1 = new ArrayCollection();
    $collection1[] = $o10 = new CollectionObject(10);
    $collection1[] = $o20 = new CollectionObject(20);
    $collection1[] = $o30 = new CollectionObject(30);
    $collection1[] = $o40 = new CollectionObject(40);
    $collection1[] = $o50 = new CollectionObject(50);
    $collection1[] = $o2 = new CollectionObject(2);
    $collection1[] = $o1 = new CollectionObject(1);
    $collection1[] = $o3 = new CollectionObject(3); 

    $collection2 = new ArrayCollection();
    $collection2[] = $o10;

    $collection2[] = $o40;
    $collection2[] = $o50;
    $collection2[] = $o2;
    $collection2[] = $o1;
    $collection2[] = $o3;
    $collection2[] = $o4 = new CollectionObject(4);
    
    $debug = function ($collection)  {
      $ret = NULL;
      foreach ($collection as $key => $object ) {
        $ret .= '['.$key.'] '.$object."\n";
      }
      return $ret;
    };
    $this->assertEquals(new ArrayCollection(array($o20, $o30)), $diff = $collection1->deleteDiff($collection2, ArrayCollection::COMPARE_OBJECTS), $debug($diff));
    $this->assertEquals(new ArrayCollection(array($o4)), $diff = $collection1->insertDiff($collection2, ArrayCollection::COMPARE_OBJECTS), $debug($diff));
  }
  
    public function testCollectionDiffBase() {
      $collection1 = new ArrayCollection();
      $collection1[] = 1;
      $collection1[] = 2;
      $collection1[] = 3;
      $collection1[] = 8;

      $collection2 = new ArrayCollection();
      $collection2[] = 4;
      $collection2[] = 5;
      $collection2[] = 6;
      $collection2[] = 8;
      
      $this->assertEquals(new ArrayCollection(array(1,2,3)), $collection1->deleteDiff($collection2, ArrayCollection::COMPARE_TOSTRING));
      $this->assertEquals(new ArrayCollection(array(4,5,6)), $collection1->insertDiff($collection2, ArrayCollection::COMPARE_TOSTRING));
    }
    
  /**
   * @group CUD
   */
  public function testComputeCUDSets() {
    $collection1 = new ArrayCollection();
    $collection1[] = $o10 = new CollectionObject(10);
    $collection1[] = $o20 = new CollectionObject(20);
    $collection1[] = $o30 = new CollectionObject(30);
    $collection1[] = $o40 = new CollectionObject(40);
    $collection1[] = $o50 = new CollectionObject(50);
    $collection1[] = $o2 = new CollectionObject(2);
    $collection1[] = $o1 = new CollectionObject(1);
    $collection1[] = $o3 = new CollectionObject(3); 

    $collection2 = new ArrayCollection();
    $collection2[] = $o10;

    $collection2[] = $o40;
    $collection2[] = $o50;
    $collection2[] = $o2;
    $collection2[] = $o1;
    $collection2[] = $o3;
    $collection2[]  = $o4 = new CollectionObject(4);
    $collection2[]  = $o110 = new CollectionObject(110);
    $collection2[]  = $o120 = new CollectionObject(120);
    $collection2[]  = $o130 = new CollectionObject(130);
    $collection2[]  = $o140 = new CollectionObject(140);
    $collection2[]  = $o150 = new CollectionObject(150);
    $collection2[]  = $o160 = new CollectionObject(160);
    $collection2[]  = $o170 = new CollectionObject(170);
    $collection2[]  = $o180 = new CollectionObject(180);
    $collection2[]  = $o190 = new CollectionObject(190);
    $collection2[]  = $o200 = new CollectionObject(200);

    list ($inserts, $updates, $deletes) = $collection1->computeCUDSets($collection2, ArrayCollection::COMPARE_OBJECTS);
    $this->assertArrayEquals(array($o20, $o30), $deletes->toArray());
    $this->assertArrayEquals(array($o4, $o110, $o120, $o130, $o140, $o150, $o160, $o170, $o180, $o190, $o200), $inserts->toArray());
    $this->assertArrayEquals(array($o10, $o40, $o50, $o2, $o1, $o3), $updates->toArray());
  }

  public function testIndexofFromDoctrine() {
    $o1 = new CollectionObject(1);
    $o2 = new CollectionObject(2);
    $o3 = new CollectionObject(3);

    $collection = new ArrayCollection(array($o1, $o2, $o3));
    $collection->removeElement($o1);

    $this->assertEquals(1, $collection->indexOf($o2)); // this is not 0
  }
}

class CollectionObject {
  
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

  /**
   * @return 0|1|-1
   */
  public function compareTo($other) {
    if ($other == NULL) throw new Exception('Cannot compare to NULL');
    if ($this->getIdentifier() === $other->getIdentifier()) return 0;
    return $this->getIdentifier() > $other->getIdentifier() ? 1 : -1;
  }
  
  public function __toString() {
    return 'Object:'.$this->id;
  }
  
  private function __clone() {
  }
}

class Stub {
  
  protected function __clone() {
  }
  
  public function isClonable() {
    return FALSE;
  }
}
