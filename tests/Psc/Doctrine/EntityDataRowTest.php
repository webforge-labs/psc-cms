<?php

namespace Psc\Doctrine;

use Psc\Doctrine\EntityDataRow;

/**
 * @group class:Psc\Doctrine\EntityDataRow
 */
class EntityDataRowTest extends \Psc\Code\Test\Base {

  public function testDumb() {
    $row = new EntityDataRow('MyEntity');
    
    $row->add('prop1','expectedString');
    $this->assertTrue($row->has('prop1'));
    $row->remove('prop1');
    $this->assertFalse($row->has('prop1'));
    
    $row->add('prop2','expectedString');
    $row->add('prop3','expectedString');

    $row->setMeta('prop2',EntityDataRow::TYPE_COLLECTION);
    $this->assertTrue($row->has('prop2'));
    $this->assertEquals(EntityDataRow::TYPE_COLLECTION, $row->getMeta('prop2'));
    
    $this->assertEquals(array('prop2','prop3'), $row->getProperties());
  }
  
  public function testConstruct() {
    $edr = new EntityDataRow('MyEntity',array('tags'=>array()));
    
    $this->assertTrue($edr->has('tags'));
    $this->assertEquals(array(),$edr->getData('tags'));
  }
  
  /**
   * @expectedException \Psc\Exception
   */
  public function testMetaNotFirst() {
    $row = new EntityDataRow('MyEntity');
    
    $row->add('prop1','expectedSTring');
    
    // ex
    $row->setMeta('prop2',EntityDataRow::TYPE_COLLECTION);
  }
}
?>