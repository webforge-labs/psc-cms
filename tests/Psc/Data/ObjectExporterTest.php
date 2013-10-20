<?php

namespace Psc\Data;

use Psc\Code\Generate\GClass;
use Webforge\Types\CollectionType;
use Webforge\Types\BirthdayType;
use Webforge\Types\ArrayType;
use Webforge\Types\ObjectType;
use Webforge\Types\DateTimeType;
use Webforge\Types\Type;
use Psc\DateTime\Date;
use Psc\DateTime\DateTime;

/**
 * @group class:Psc\Data\ObjectExporter
 */
class ObjectExporterTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\ObjectExporter';
    parent::setUp();
    $this->objectExporter = new ObjectExporter();
  }
  
  public function testConstruct() {
    $objectExporter = new ObjectExporter();
    
    $t = function ($type) { return Type::create($type); };

    $set = new Set(); // set implements Walkable
    $set->set('name','oids_pages', $t('String'));
    
    $j1 = new Set();
    $j1->set('name', 'oid', $t('String'));
    $j1->set('referencedColumnName', 'oid', $t('String'));
    $j1->set('onDelete', 'cascade', $t('String'));

    $j2 = new Set();
    $j2->set('name', 'page_id', $t('String'));
    $j2->set('referencedColumnName', 'id', $t('String'));
    $j2->set('onDelete', 'cascade', $t('String'));
    
    $set->set('joinColumns', array($j1, $j2), new ArrayType(new ObjectType(new GClass('Psc\Data\Walkable'))));
    $set->set('inverseJoinColumns', array($j2, $j1), new ArrayType(new ObjectType(new GClass('Psc\Data\Walkable'))));
    
    $this->assertInstanceOf('stdClass', $object = $objectExporter->walkWalkable($set));
    $this->assertInternalType('array', $object->joinColumns);
    $this->assertInternalType('array', $object->inverseJoinColumns);
    $this->assertInstanceOf('stdClass', $object->joinColumns[0]);
    $this->assertInstanceOf('stdClass', $object->inverseJoinColumns[0]);
  }
  
  public function testCollectionWalking() {
    $collection = new \Doctrine\Common\Collections\ArrayCollection(array('some','inner','items'));
    $this->assertInternalType('array', $collectionExport = $this->objectExporter->walk($collection, $this->objectExporter->inferType($collection)));
    $this->assertEquals($collectionExport, array('some','inner','items'));
  }
  
  public function testCollectionWalkingType() {
    $collection = new \Doctrine\Common\Collections\ArrayCollection(array('some','inner','items'));
    $this->assertInternalType(
      'array',
      $collectionExport = $this->objectExporter->walk($collection,
        new CollectionType(CollectionType::DOCTRINE_ARRAY_COLLECTION)
      )
    );
    $this->assertEquals($collectionExport, array('some','inner','items'));
  }
  
  public function testDateExporting() {
    $birthdayType = new BirthdayType();
    $birthday = new Date('21.11.1984');
    
    $this->assertEquals(
      (object) array(
        'date'=>$birthday->getTimestamp(),
        'timezone'=>date_default_timezone_get()
      ),
      $this->objectExporter->walk($birthday, $birthdayType)
    );
  }

  public function testDateTimeExporting() {
    $type = new DateTimeType();
    $value = new DateTime('21.11.1984 17:21:00');
    
    $this->assertEquals(
      (object) array(
        'date'=>$value->getTimestamp(),
        'timezone'=>date_default_timezone_get()
      ),
      $this->objectExporter->walk($value, $type)
    );
  }

  public function testEmptyCollectionExportAsArray() {
    $objectExporter = new ObjectExporter();
    
    $collection = new \Psc\Data\ArrayCollection(array());
    $this->assertEquals(array(), $objectExporter->walk($collection, $this->createType('Collection<String>')));
  }
}
