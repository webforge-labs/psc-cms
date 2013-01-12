<?php

namespace Psc\Data\Type;

use Psc\Data\Type\Type;

/**
 * @group class:Psc\Data\Type\Type
 */
class TypeTest extends \Psc\Code\Test\Base {

  public function testAPI() {
    $type1 = new CustomStringType();      
    $this->assertEquals('CustomString',$type1->getName());
    $this->assertInstanceOf('Psc\Data\Type\CustomStringType', $type1);
    
    $type2 = Type::create('CustomString');
    $this->assertInstanceOf('Psc\Data\Type\CustomStringType', $type2);
    $this->assertEquals('CustomString',$type2->getName());

    $this->assertNotSame($type1,$type2);
    
    $type3 = CustomStringType::create();
    $this->assertInstanceOf('Psc\Data\Type\CustomStringType', $type3);
    $this->assertEquals('CustomString',$type3->getName());
    
    
    $this->assertException('Psc\Data\Type\Exception', function () {
      $type4 = \Psc\Data\Type\Type::create();
    });
  }
  
  public function testArgsCreation_withEmptyArgs() {
    $arrayType = Type::createArgs('Array', array());
    
    $arrayType = Type::createArgs('Array', array($innerType = Type::create('String')));
    $this->assertSame($arrayType->getType(), $innerType);
  }
  
  public function testAdvancedCreation_object() {
    $objectType = Type::create('Object<Psc\Exception>');
    $this->assertInstanceOf('Psc\Data\Type\ObjectType', $objectType);
    $this->assertEquals('Psc\Exception', $objectType->getGClass()->getFQN());
  }

  public function testAdvancedCreation_array() {
    $arrayType = Type::create('String[]');
    $this->assertInstanceOf('Psc\Data\Type\ArrayType', $arrayType);
    $this->assertEquals('String', $arrayType->getType()->getName());
  }

  public function testAdvancedCreation_objectInArray() {
    $arrayType = Type::create('Object<Psc\Exception>[]');
    $this->assertInstanceOf('Psc\Data\Type\ArrayType', $arrayType);
    $this->assertInstanceOf('Psc\Data\Type\ObjectType', $objectType = $arrayType->getType());
    $this->assertEquals('Psc\Exception', $objectType->getGClass()->getFQN());
  }
  
  /**
   * @dataProvider docBlockTypes
   */
  public function testDocBlockParsing($expectedTypeFQN, $docBlockWord) {
    $this->assertInstanceof($expectedTypeFQN, Type::parseFromDocBlock($docBlockWord));
  }
  
  public static function docBlockTypes() {
    $tests = array();
    $t = function ($name) {
      return 'Psc\Data\Type\\'.$name.'Type';
    };
    
    $tests[] = array(
      $t('Integer'),
      'integer'
    );

    $tests[] = array(
      $t('String'),
      'string'
    );

    $tests[] = array(
      $t('Mixed'),
      'mixed'
    );

    $tests[] = array(
      $t('Object'),
      'stdClass'
    );

    $tests[] = array(
      $t('Object'),
      'object'
    );

    $tests[] = array(
      $t('Array'),
      'array'
    );

    $tests[] = array(
      $t('Array'),
      'string[]'
    );

    $tests[] = array(
      $t('Array'),
      'integer[]'
    );
    
    return $tests;
  }
}

class CustomStringType extends Type {
  
}
?>