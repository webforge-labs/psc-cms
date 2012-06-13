<?php

namespace Psc\Data;

use Psc\Data\SetMeta;
use Psc\Data\Type\StringType;
use Psc\Data\Type\ArrayType;
use Psc\Data\Type\IntegerType;
use Psc\Data\Type\SmallIntegerType;

/**
 * @group class:Psc\Data\SetMeta
 */
class SetMetaTest extends \Psc\Code\Test\Base {

  public function testConstruct() {
    $meta = new SetMeta(array(
      'label' => new StringType(),
      'numbers' => new ArrayType(new IntegerType())
    ));
    
    return $meta;
  }
  
  /**
   * @expectedException Psc\Data\Type\TypeExpectedException
   * @depends testConstruct
   */
  public function testSetTypesFromArrayThrowsTypeExpectedException(SetMeta $meta) {
    $meta->setTypesFromArray(array(
      'label'=> new StringType(), // das ist okay
      'numbers'=> 'ichbinkeinobjekt', // das ist nicht okay
    ));
  }
  
  /**
   * @depends testConstruct
   */
  public function testSetAndGetFieldType(SetMeta $meta) {
    $this->assertInstanceOf('Psc\Data\SetMeta',
                            $meta->setFieldType('tiny', $sit = new SmallIntegerType()));
    
    $this->assertInstanceOf('Psc\Data\Type\Type',
                            $type = $meta->getFieldType('tiny'));
    $this->assertSame($sit,$type);


    $this->assertInstanceOf('Psc\Data\SetMeta',
                            $meta->setFieldType('seconddim.tiny', $sit2 = new SmallIntegerType()));
    
    $this->assertInstanceOf('Psc\Data\Type\Type',
                            $type = $meta->getFieldType(array('seconddim','tiny')));
    $this->assertSame($sit2,$type);
  }

  /**
   * @depends testConstruct
   */
  public function testGetTypes() {
    $meta = $this->testConstruct();
    
    $meta->setFieldType(array('multi',0), new StringType());
    $meta->setFieldType(array('multi',1), new StringType());
    $meta->setFieldType(array('multi',2), new StringType());
    $meta->setFieldType(array('multi',3, 'first'), new StringType());
    $meta->setFieldType(array('multi',3, 'second'), new SmallIntegerType());
    $meta->setFieldType(array('multi',3, 'third'), new StringType());
    
    $this->assertEquals(array(
      'label'=> new StringType(),
      'numbers' => new ArrayType(new IntegerType()),
      'multi.0' => new StringType(),
      'multi.1' => new StringType(),
      'multi.2' => new StringType(),
      'multi.3.first' => new StringType(),
      'multi.3.second' => new SmallIntegerType(),
      'multi.3.third' => new StringType(),
    ), $meta->getTypes());
  }
  
  /**
   * @expectedException Psc\Data\FieldNotDefinedException
   * @depends testConstruct
   */
  public function testGetFieldTypeThrowsFieldNotDefinedException(SetMeta $meta) {
    $meta->getFieldType('ich.bin.nicht.vorhanden');
  }

  /**
   * @expectedException Psc\Data\FieldNotDefinedException
   * @depends testConstruct
   */
  public function testGetFieldTypeThrowsFieldNotDefinedException_forRoots() {
    $meta = $this->testConstruct();
    $meta->setFieldType(array('multi',0), new StringType());
    $meta->setFieldType(array('multi',1), new StringType());

    $meta->getFieldType(array('multi'));
  }
  
  /**
   * @depends testConstruct
   */
  public function testGetFieldTypeThrowsFieldNotDefinedException_WithFieldname(SetMeta $meta) {
    $e = $this->assertException('Psc\Data\FieldNotDefinedException', function () use ($meta) {
      $meta->getFieldType(array('ich','bin','nicht','da'));
    });
    
    $this->assertEquals(array('ich','bin','nicht','da'), $e->field);
  }
}
?>