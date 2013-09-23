<?php

namespace Psc\Data\Type;

use Psc\Data\Type\ObjectType;
use Psc\Code\Generate\GClass;

/**
 * @group class:Psc\Data\Type\ObjectType
 */
class ObjectTypeTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\ObjectType';
  }
  
  public function testConstruct() {
    $type = new ObjectType(); // Ok
    
    $type = new ObjectType($gc = new GClass('stdClass'));
    $this->assertSame($gc, $type->getClass());
  }
  
  public function testExpandNamespaceWithNoFQN() {
    $type = new ObjectType(new GClass('LParameter'));
    $type->expandNamespace('Psc\Code\AST');
    
    $this->assertEquals('Psc\Code\AST\LParameter', $type->getClassFQN());
  }
  
  public function testExpandNamespaceWithFQN() {
    $type = new ObjectType($gc = new GClass('\LParameter'));
    $type->expandNamespace('Psc\Code\AST');
    $this->assertEquals('LParameter', $type->getClassFQN());
  }

  public function testSetAndGetClass() {
    $type = new ObjectType();
    
    $this->assertChainable($type->setClass($nc = new GClass('Psc\Data\Set')));
    $this->assertInstanceOf('Psc\Code\Generate\GClass',$gc = $type->getClass());
    $this->assertSame($nc, $gc);
  }
  
  public function testPHPType() {
    $type = Type::create('Object')->setClass(new GClass('Psc\Data\Set'));
    
    $this->assertEquals('Psc\Data\Set',$type->getPHPType());
  }

  public function testPHPHint() {
    $type = Type::create('Object')->setClass(new GClass('Psc\Data\Set'));
    
    $this->assertEquals('\Psc\Data\Set',$type->getPHPHint());
  }

  public function testPHPHintWithoutClass() {
    $type = Type::create('Object');
    
    $this->assertEquals('\stdClass', $type->getPHPHint());
  }
  
  public function testPHPHintWithNamespaceContext() {
    $type = Type::create('Object')->setClass(new GClass('Webforge\Common\System\Dir'));
    
    $this->assertEquals('Dir',$type->getPHPHint('Webforge\Common\System'));
  }

  public function testIsParameterHintedTypeForTypeWithGClass() {
    $type = Type::create('Object')->setClass($gClass = new GClass('Webforge\Common\System\Dir'));
    
    $this->assertInstanceOf('Psc\Data\Type\ParameterHintedType', $type);
    $this->assertEquals('\Webforge\Common\System\Dir', $type->getParameterHint($useFQN = TRUE));
    $this->assertEquals('Dir', $type->getParameterHint($useFQN = FALSE));
    
    $this->assertSame($gClass, $type->getParameterHintImport());
  }
  
  public function testIsParameterHintedTypeForType() {
    $type = Type::create('Object');
    
    $this->assertInstanceOf('Psc\Data\Type\ParameterHintedType', $type);
    $this->assertEquals('\stdClass', $type->getParameterHint($useFQN = TRUE));
    $this->assertEquals('stdClass', $type->getParameterHint($useFQN = FALSE));
    
    $this->assertNull($type->getParameterHintImport());
  }
}
?>