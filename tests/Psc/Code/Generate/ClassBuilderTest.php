<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\ClassBuilder;
use Webforge\Types\Type;
use Webforge\Types\MarkupTextType;
use Psc\Code\Callback;

/**
 * @group generate
 * @group class:Psc\Code\Generate\ClassBuilder
 * @group entity-building
 */
class ClassBuilderTest extends \Psc\Code\Test\Base {

  protected $cProperty = 'Psc\Code\Generate\ClassBuilderProperty';
  protected $cBuilder = 'Psc\Code\Generate\ClassBuilder';
  
  protected $classBuilder;
  
  public function setUp() {
    $this->classBuilder = new ClassBuilder(new GClass('\Psc\TPL\SC\Teaser'));
    $this->chainClass = $this->cBuilder;
  }

  public function testAddMethod() {
    $this->assertInstanceOf('Psc\Code\Generate\GMethod', $this->classBuilder->addMethod(new GMethod('__construct')));
    $this->assertTrue($this->classBuilder->getGClass()->hasMethod('__construct'));
  }
  
  public function testCreateMethod() {
    $this->assertInstanceOf('Psc\Code\Generate\GMethod', $this->classBuilder->createMethod('__construct'));
    $this->assertTrue($this->classBuilder->getGClass()->hasMethod('__construct'));
  }
  
  public function testCreateClassDocBlock_doesNotRecreate() {
    $dbExpected = $this->classBuilder->createClassDocBlock();
    $dbActual = $this->classBuilder->createClassDocBlock();
    $this->assertSame($dbExpected, $dbActual);
  }
  
  public function testConstructWithNotEmptyGClass_doeswhat() {
    // what does it?
  }
  
  // dies ist gleichzeitig ein test für den "cannot determine default for internal functions" fallback
  public function testPropertyElevation() {
    $gClass = new GClass('Psc\OtherException');
    $parentClass = new GClass('Psc\Exception');
    
    $classBuilder = new ClassBuilder($gClass);
    $classBuilder->setParentClass($parentClass);
    
    $this->assertInstanceOf('Psc\Code\Generate\ClassBuilderProperty',$classBuilder->getProperty('message'));
  }
  
  public function testElevatedPropertyIsNotWrittenInChildClass() {
    $this->markTestIncomplete('@TODO');
  }
}
?>