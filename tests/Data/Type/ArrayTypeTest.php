<?php

namespace Psc\Data\Type;

use Psc\Data\Type\ArrayType;

/**
 * @group class:Psc\Data\Type\ArrayType
 */
class ArrayTypeTest extends \Psc\Code\Test\Base {

  public function testConstruct() {
    $array = new ArrayType();
    
    $this->assertFalse($array->isTyped());
    
    $typedArray = new ArrayType($it = new IntegerType());
    $this->assertTrue($typedArray->isTyped());
    $this->assertSame($it, $typedArray->getType());
    
    return $typedArray;
  }
  
  public function testThatIsListIsNotSetAfterConstruct() {
    $type = new ArrayType();
    $this->assertSame(NULL, $type->isList()); // means: no decision was met!
    $this->assertSame(NULL, $type->setIsList(NULL)->isList());
  }
  
  /**
   * @depends testConstruct
   */
  public function testSetAndGetType(ArrayType $typedArray) {
    $it = $typedArray->getType();
    $this->assertInstanceOf('Psc\Data\Type\IntegerType',$it); // doppelt gemoppelt von testConstruct
    
    $typedArray->setType($it);
    $this->assertTrue($typedArray->isTyped());
    $this->assertSame($it, $typedArray->getType());
    
    $typedArray->setType(NULL);
    $this->assertFalse($typedArray->isTyped());
    return $typedArray; // ist nicht mehr wirklich typed
  }
  
  /**
   * @depends testSetAndGetType
   * @expectedException Psc\Data\Type\ArrayNotTypedException
   */
  public function testGetTypeThrowsArrayNotTypedExceptionWhenTypeisNULL(ArrayType $array) {
    $array->getType();
  }
}
?>