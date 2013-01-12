<?php

namespace Psc\Doctrine;

use Psc\Doctrine\AbstractEntity;

/**
 * @group class:Psc\Doctrine\AbstractEntity
 */
class AbstractEntityTest extends \Psc\Code\Test\Base {

  public function testxx() {
    $entity = $this->getInstance();
  }
  
  public function testEquals() {
    $e7 = $this->getInstance(7);
    $e2 = $this->getInstance(2);
    $e2i2 = $this->getInstance(2); // instance2 von id: 2
    
    // gleichheit
    $this->assertTrue($e2->equals($e2i2));
    $this->assertTrue($e2i2->equals($e2));
    
    // ungleichheit
    $this->assertFalse($e7->equals($e2));
    $this->assertFalse($e2->equals($e7));
    
    // ungleichheit gegenüber NULL
    $emptyEntity = $this->getInstance(NULL);
    $this->assertFalse($emptyEntity->equals(NULL));
    
    // ungleichheit gegenüber falschem Typ
    $otherEntity = $this->getMock('Psc\Doctrine\AbstractEntity');
    $otherEntity->expects($this->any())
                ->method('getIdentifier')
                ->will($this->returnValue(400));

    $this->assertFalse($e2->equals($otherEntity));
    $this->assertFalse($emptyEntity->equals($otherEntity));
  }
  
  public function testIsNew() {
    $entity = $this->getInstance(NULL);
    $this->assertTrue($entity->isNew());
    
    $entity= $this->getInstance(7);
    $this->assertFalse($entity->isNew());
  }
  
  public function testGetEntityLabel() {
    $entity = $this->getInstance(23939);
    
    $this->assertNotEmpty($entity->getEntityLabel());
    
    // 'Psc\Doctrine\Entity<tiptoi\Entities\Sound> [23939] Graubübül');
    $this->assertEquals('Psc\Doctrine\Entity<Psc\Doctrine\MyEntity> [23939]', $entity->getEntityLabel());
  }
  
  protected function getInstance($identifier = NULL) {
    return new MyEntity($identifier);
  }
}

// Unser schöner Mock
class MyEntity extends AbstractEntity {
  
  protected $identifier;
  
  public function __construct($identifier = NULL) {
    $this->identifier = $identifier;
  }
  
  public function getIdentifier() {
    return $this->identifier;
  }

  public function setIdentifier($id) {
    $this->identifier = $id;
    return $this;
  }
  
  public function getEntityName() {
    return 'Psc\Doctrine\MyEntity';
  }
}
?>