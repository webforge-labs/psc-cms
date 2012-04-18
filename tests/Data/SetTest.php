<?php

namespace Psc\Data;

use Psc\Data\Set;
use Psc\Data\Type\StringType;
use Psc\Data\Type\ArrayType;
use Psc\Data\Type\SmallIntegerType;
use Psc\Data\Type\IntegerType;

class SetTest extends \Psc\Code\Test\Base {
  
  public function testEmptyConstruct() {
    $set = new Set();
    
    $this->assertInstanceOf('Psc\Data\SetMeta',$set->getMeta());
  }

  public function testConstruct() {
    $meta = new SetMeta(array(
      'label' => new StringType(),
      'numbers' => new ArrayType(new IntegerType()),
      'second.tiny' => new SmallIntegerType()
    ));

    $set = new Set(
                   array('label'=>'Das Eichhörnchen',
                         'numbers'=>array(1,5,8),
                         'second.tiny'=>12
                         ),
                   $meta
                   );
    
    $this->assertEquals('Das Eichhörnchen', $set->get('label'));
    $this->assertEquals(array(1,5,8), $set->get('numbers'));
    $this->assertEquals(12, $set->get(array('second','tiny')));
    
    return $set;
  }
  
  public function testCreateFromStruct() {
    $expectedSet = $this->testConstruct();
    
    $set = Set::createFromStruct(Array(
      'label' => array('Das Eichhörnchen', new StringType()),
      'numbers'=> array(array(1,5,8), new ArrayType(new IntegerType())),
      'second.tiny'=> array(12, new SmallIntegerType())
    ));
    
    $this->assertEquals($expectedSet, $set);
  }
  
  /**
   * @depends testConstruct
   */
  public function testSetAndGet(Set $set) {
    $set->set('label', 'Das Eichoernchen');
    $this->assertEquals('Das Eichoernchen', $set->get('label'));

    $set->set('second.tiny', 13);
    $this->assertEquals(13, $set->get(array('second','tiny')));

    $set->set('numbers', array(1));
    $this->assertEquals(array(1), $set->get(array('numbers')));
    
    $set->set('otherField','mystring', new StringType());
    $this->assertEquals('mystring',$set->get('otherField'));
    $this->assertInstanceOf('Psc\Data\Type\StringType',$set->getMeta()->getFieldType('otherField'));

    $set->set('otherField',7, new IntegerType());
    $this->assertEquals(7,$set->get('otherField'));
    $this->assertInstanceOf('Psc\Data\Type\IntegerType',$set->getMeta()->getFieldType('otherField'));
  }
  
  
  public function testKeys() {
    $meta = new SetMeta(array(
      0=>new ArrayType(),
      '0.label' => new StringType(),
      '0.number' => new SmallIntegerType(),
      1=>new ArrayType(),
      '1.label' => new StringType(),
      '1.number' => new SmallIntegerType()
    ));

    $set = new Set(Array(
      '0'=>array(
        'label'=>'C1',
        'number'=>1
      ),
      1=>array( // ob integer oder string does not matter
        'label'=>'C2',
        'number'=>2
      )
    ), $meta);
    
    $this->assertEquals(array('0','0.label','0.number','1','1.label','1.number'),
                        $set->getKeys()
                       );
    $this->assertEquals(array(0,1),
                        $set->getRootKeys()
                       );
  }
  
  /**
   * @expectedException Psc\Data\FieldNotDefinedException
   * @depends testConstruct
   */
  public function testSetWithoutMetaThrowsException(Set $set) {
    $set->set('missinglabel','Das Eichhoernchen');
  }
  
  /**
   * @expectedException Psc\Data\FieldNotDefinedException
   * @depends testConstruct
   */
  public function testSetFieldsFromArrayWithoutMetaThrowsException(Set $set) {
    $set->setFieldsFromArray(array('missinglabel2'=>'Das Eichhoernchen'));
  }
  

  /**
   * @expectedException Psc\Data\FieldNotDefinedException
   * @depends testConstruct
   */
  public function testGetWithoutMetaThrowsException(Set $set) {
    $set->get('missinglabel');
  }

  /**
   * @depends testConstruct
   */
  public function testGetWithMetaButEmptyReturnsNULL(Set $set) {
    $set->getMeta()->setFieldType('withMetaButEmptyLabel',new StringType());
    
    $this->assertSame(NULL,$set->get('withMetaButEmptyLabel'));
  }
}
?>