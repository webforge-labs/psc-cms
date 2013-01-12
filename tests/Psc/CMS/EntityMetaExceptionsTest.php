<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\EntityMeta
 */
class EntityMetaExceptionsTest extends \Psc\Code\Test\Base {
  
  protected $entityMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityMeta';
    parent::setUp();
    $this->classMetadata = $this->getMock('Doctrine\ORM\Mapping\ClassMetadata', array('getIdentifierFieldNames'), array('Psc\Doctrine\TestEntitites\Article'));
    $this->classMetadata->expects($this->any())->method('getIdentifierFieldNames')->will($this->returnValue(array('id')));
    $this->entityMeta = new EntityMeta('Psc\Doctrine\TestEntities\Article', $this->classMetadata, 'Artikel');
    
    $this->entity = current($this->loadTestEntities('articles'));
  }
  
  /**
   * @expectedException Psc\Doctrine\FieldNotDefinedException
   */
  public function testOnGetPropertyMeta_FieldNotDefinedException() {
    $this->entityMeta->getPropertyMeta('thisfieldIsReallyNotAnExistingOne');
  }
}
?>