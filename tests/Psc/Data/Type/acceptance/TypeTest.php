<?php

namespace Psc\Data\Type;

use Psc\Data\Type\Type;

/**
 * @group class:Psc\Data\Type\Type
 */
class TypeTest extends \Psc\Code\Test\Base {

  public function testArgsCreation_withEmptyArgs() {
    $arrayType = Type::createArgs('Array', array());
    
    $arrayType = Type::createArgs('Array', array($innerType = Type::create('String')));
    $this->assertSame($arrayType->getType(), $innerType);
  }
  
  public function testAdvancedCreation_object() {
    $objectType = Type::create('Object<Psc\Exception>');
    $this->assertInstanceOf('Webforge\Types\ObjectType', $objectType);
    $this->assertEquals('Psc\Exception', $objectType->getGClass()->getFQN());
  }

  public function testAdvancedCreation_array() {
    $arrayType = Type::create('String[]');
    $this->assertInstanceOf('Webforge\Types\ArrayType', $arrayType);
    $this->assertEquals('String', $arrayType->getType()->getName());
  }

  public function testAdvancedCreation_objectInArray() {
    $arrayType = Type::create('Object<Psc\Exception>[]');
    $this->assertInstanceOf('Webforge\Types\ArrayType', $arrayType);
    $this->assertInstanceOf('Webforge\Types\ObjectType', $objectType = $arrayType->getType());
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
      return 'Webforge\Types\\'.$name.'Type';
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
