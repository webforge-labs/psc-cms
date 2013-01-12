<?php

namespace Psc\Doctrine;

use Psc\Data\ArrayCollection;

/**
 * @group class:Psc\Doctrine\GenericCollectionSynchronizer
 *
 * @TODO test ob alle callbacks gecalled werden
 */
class GenericCollectionSynchronizerTest extends \Psc\Code\Test\Base {
  
  protected $synchronizer;
  protected $evm;
  protected $fromCollection;
  protected $toCollection;
  
  protected $updates;
  protected $inserts;
  protected $deletes;
  
  protected $objectClass; // CollectionObject
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\GenericCollectionSynchronizer';
    $this->objectClass = __NAMESPACE__.'\CollectionObject';
    parent::setUp();
    $this->evm = new \Psc\Code\Event\Manager();
    $this->synchronizer = new GenericCollectionSynchronizer(array(),$this->evm);
    $this->initCollections();
  }
  
  public function assertPreConditions() {
    list($inserts, $updates, $deletes) = $this->synchronizer->computeCUDSets($this->fromCollection, $this->toCollection);
    $this->assertArrayEquals($this->updates->toArray(), $updates->toArray());
    $this->assertArrayEquals($this->inserts->toArray(), $inserts->toArray());
    $this->assertArrayEquals($this->deletes->toArray(), $deletes->toArray());
  }
  
  public function testConstruct() {
    $this->assertSame($this->evm, $this->synchronizer->getManager());
  }
  
  public function testFiresAllEventsOnListeners() {
    $listener = $this->getMock('Psc\Doctrine\EventCollectionSynchronizerListener', array('onCollectionUpdate',
                                                                                   'onCollectionDelete',
                                                                                   'onCollectionInsert'
                                                                                   ));
    
    $listener->expects($this->exactly(count($this->inserts)))->method('onCollectionInsert')
             ->with($this->isInstanceOf($this->objectClass), $this->isInstanceOf('Psc\Code\Event\Event'));
    $listener->expects($this->exactly(count($this->updates)))->method('onCollectionUpdate')
             ->with($this->isInstanceOf($this->objectClass), $this->isInstanceOf('Psc\Code\Event\Event'));
    $listener->expects($this->exactly(count($this->deletes)))->method('onCollectionDelete')
             ->with($this->isInstanceOf($this->objectClass), $this->isInstanceOf('Psc\Code\Event\Event'));
    
    $this->synchronizer->subscribe($listener);
    
    $this->synchronizer->process($this->fromCollection, $this->toCollection);
  }
  
  public function initCollections() {
    $this->fromCollection = new ArrayCollection();
    $this->fromCollection[] = $o10 = new CollectionObject(10);
    $this->fromCollection[] = $o20 = new CollectionObject(20);
    $this->fromCollection[] = $o30 = new CollectionObject(30);
    $this->fromCollection[] = $o40 = new CollectionObject(40);
    $this->fromCollection[] = $o50 = new CollectionObject(50);
    $this->fromCollection[] = $o2 = new CollectionObject(2);
    $this->fromCollection[] = $o1 = new CollectionObject(1);
    $this->fromCollection[] = $o3 = new CollectionObject(3); 

    $this->toCollection = new ArrayCollection();
    // o20, o30 fehlen
    
    // updates
    $this->toCollection[] = $o10;
    $this->toCollection[] = $o40;
    $this->toCollection[] = $o50;
    $this->toCollection[] = $o2;
    $this->toCollection[] = $o1;
    $this->toCollection[] = $o3;
    
    // o4,o5 sind neu
    $this->toCollection[] = $o4 = new CollectionObject(4);
    $this->toCollection[] = $o5 = new CollectionObject(5);
    
    $this->updates = new ArrayCollection(array($o10, $o40, $o50, $o2, $o1, $o3));
    $this->deletes = new ArrayCollection(array($o20, $o30));
    $this->inserts = new ArrayCollection(array($o4, $o5));
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
}
?>