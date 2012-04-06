<?php

namespace Psc\Doctrine;

use Psc\Doctrine\EntityDataRow;

class TestSoundEntity extends \Psc\Doctrine\Object {
  
  protected $number = NULL;
  protected $label = NULL;
  protected $tags = NULL;
  protected $content = NULL;
  
  public function getEntityName() {
    return 'Psc\Doctrine\TestSoundEntity';
  }
  
  public function getIdentifier() {
    return $this->id;
  }
}

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
  
  public function testEntity() {
    $dataRow = new EntityDataRow('Psc\Doctrine\TestSoundEntity', Array(
      'number'=>NULL,
      'label'=>NULL,
      'tags'=>NULL,
      'content'=>'Das Eichhörnchen (Sciurus vulgaris), regional auch Eichkätzchen, Eichkater oder niederdeutsch Katteker, ist ein Nagetier aus der Familie der Hörnchen (Sciuridae). Es ist der einzige natürlich in Mitteleuropa vorkommende Vertreter aus der Gattung der Eichhörnchen und wird zur Unterscheidung von anderen Arten wie dem Kaukasischen Eichhörnchen und dem in Europa eingebürgerten Grauhörnchen auch als Europäisches Eichhörnchen bezeichnet.'
    ));
    
    $soundEntity = new TestSoundEntity();
    $soundEntity->setContent('Das Eichhörnchen (Sciurus vulgaris), regional auch Eichkätzchen, Eichkater oder niederdeutsch Katteker, ist ein Nagetier aus der Familie der Hörnchen (Sciuridae). Es ist der einzige natürlich in Mitteleuropa vorkommende Vertreter aus der Gattung der Eichhörnchen und wird zur Unterscheidung von anderen Arten wie dem Kaukasischen Eichhörnchen und dem in Europa eingebürgerten Grauhörnchen auch als Europäisches Eichhörnchen bezeichnet.');
    
    // wurde aus der Klasse entfernt
    ///* Sucessful Test */
    //$test = new DummyTestCase($dataRow, $soundEntity);
    //$test->runBare();
    //$this->assertFalse($test->hasFailed());
    //
    ///* Failing Test */
    //$soundEntity->setLabel('Wikipedia Text: das Eichhörnchen');
    //$test = new DummyTestCase($dataRow, $soundEntity);
    //$test->run();
    //$this->assertTrue($test->hasFailed());
  }
}


class DummyTestCase extends \Psc\Code\Test\Base {
  
  protected $row;
  protected $entity;
  
  public function __construct(EntityDataRow $row = NULL, \Psc\Doctrine\Object $entity = NULL) {
    $this->row = $row;
    $this->entity = $entity;
    
    parent::__construct('testMain');
  }
  
  public function testMain() {
    $this->row->assert($this->entity, $this);
  }
}

?>