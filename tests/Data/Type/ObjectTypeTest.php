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
}
?>