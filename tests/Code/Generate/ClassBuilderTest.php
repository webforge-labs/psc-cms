<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\ClassBuilder;
use Psc\Data\Type\Type;
use Psc\Data\Type\MarkupTextType;
use Psc\Code\Callback;

/**
 * @group generate
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

  public function testAddProperty() {
    $cb = $this->classBuilder;
    
    $cb->addProperty('thumb')
         ->setType(Type::create('Image'))
       ->addProperty('image')
         ->setType(new \Psc\Data\Type\ImageType())
       ->addProperty('text')
         ->setType(MarkupTextType::create())
       ->addProperty('link')
         ->setType(Type::create('Link'))
      ;
      
    $this->assertCount(4, $cb->getProperties());
    
    $this->assertInstanceof($this->cProperty, $cb->getProperty('image'));
    return $cb;
  }
  
  /**
   * @expectedException Psc\Code\Generate\ClassBuilderException
   */
  public function testAddProperty_throwsExceptionWhenDouble() {
    $this->classBuilder->addProperty('numb');
    $this->classBuilder->addProperty('numb');
  }
  
  /**
   * @depends testAddProperty
   */
  public function testSetProperties($cb) {
    
    $properties = array();
    foreach ($cb->getProperties() as $param) {
      $properties[] = $param;
    }
    
    $testCB = new ClassBuilder(new GClass('\Psc\TPL\SC\Teaser'));
    $testCB->setProperties($properties);
    
    $this->assertEquals($cb, $testCB);
  }
  
  public function testHasProperty() {
    $cb = $this->classBuilder;
    $cb->addProperty('thumb');
    $this->assertTrue($cb->hasProperty('thumb'));
    $this->assertTrue($cb->hasProperty('Thumb'));
  }
  
  /**
   * @depends testAddProperty
   */
  public function testGenerateGetters(ClassBuilder $cb) {
    $this->assertChainable(
      $cb->generateGetters()
    );
      
    $gClass = $cb->getGClass();
    $this->assertTrue($gClass->hasMethod('getImage'));
    $this->assertTrue($gClass->hasMethod('getText'));
    $this->assertTrue($gClass->hasMethod('getLink'));
    $this->assertTrue($gClass->hasMethod('getThumb'));
    
    $getThumb = $gClass->getMethod('getThumb');
    $this->assertEquals(<<<'__PHP__'
public function getThumb() {
  return $this->thumb;
}
__PHP__
      ,
      $getThumb->php(0)
    );
  }
  
  /**
   * @depends testAddProperty
   */
  public function testGenerateSetters(ClassBuilder $cb) {
    $this->assertChainable(
      $cb->generateSetters()
    );

    $gClass = $cb->getGClass();
    $this->assertTrue($gClass->hasMethod('setImage'));
    $this->assertCount(1, $gClass->getMethod('setImage')->getParameters());
    $this->assertTrue($gClass->hasMethod('setText'));
    $this->assertCount(1, $gClass->getMethod('setText')->getParameters());
    $this->assertTrue($gClass->hasMethod('setLink'));
    $this->assertCount(1, $gClass->getMethod('setLink')->getParameters());
    $this->assertTrue($gClass->hasMethod('setThumb'));
    $this->assertCount(1, $gClass->getMethod('setThumb')->getParameters());

    $setThumb = $gClass->getMethod('setThumb');
    $this->assertEquals(<<< '__PHP__'
public function setThumb($thumb) {
  $this->thumb = $thumb;
  return $this;
}
__PHP__
      ,
      $setThumb->php(0)
    );
  }
  
  
  /**
   * @depends testAddProperty
   */
  public function testGeneratePropertiesConstructor(ClassBuilder $cb) {
    $this->assertChainable(
      $cb->generatePropertiesConstructor(array(
        $cb->getProperty('image'),
        $cb->getProperty('text'),
        $cb->getProperty('link')
      ))
    );
    
    $this->assertTrue($cb->getGClass()->hasMethod('__construct'));
    $m = $cb->getGClass()->getMethod('__construct');
    $this->assertCount(3, $m->getParameters());

    list ($p1,$p2,$p3) = $m->getParameters();
    $this->assertEquals('image',$p1->getName());
    $this->assertNull($p1->getHint());
    $this->assertEquals('text',$p2->getName());
    $this->assertNull($p1->getHint());
    $this->assertEquals('link',$p3->getName());
    $this->assertEquals('Psc\Data\Type\Interfaces\Link',$p3->getHint()->getFQN());
    
    $constructorPHP = <<< 'PHP'
  $this->setImage($image);
  $this->setText($text);
  $this->setLink($link);

PHP;
    
    $this->assertEquals($constructorPHP, $m->getBody());
    //print $cb->getGClass()->php();
  }
  
  public function testAddMethod() {
    $this->assertInstanceOf('Psc\Code\Generate\GMethod', $this->classBuilder->addMethod(new GMethod('__construct')));
    $this->assertTrue($this->classBuilder->getGClass()->hasMethod('__construct'));
  }
  
  public function testCreateMethod() {
    $this->assertInstanceOf('Psc\Code\Generate\GMethod', $this->classBuilder->createMethod('__construct'));
    $this->assertTrue($this->classBuilder->getGClass()->hasMethod('__construct'));
  }
  
  
  public function testMethodSorting() {
    /* @TODO implement
      noch nicht implementiert, denn
      
      1. soll das in den ClassWriter in die GClass oder Hierhin? (eher GClass)
      2. da GClass und so immer nach Namen indizieren is das hier schwierig
      3. bei GClass->getMethods() werden nicht alle zurückgegeben, was soll also mit den "unsortierten" passieren?
    */
    $this->markTestSkipped('noch nicht implementiert');
  
    $construct = $this->classBuilder->createMethod('getBanane');
    $banane = $this->classBuilder->createMethod('getZeppelin');
    $zeppelin = $this->classBuilder->createMethod('__construct');
    
    // constructor soll nach oben
    $cb = new Callback(function ($methods) {
      return array($methods['__construct'],
                   $methods['getBanane'],
                   $methods['getZeppelin']);
    });
    
    $this->classBuilder->sortMethods($cb);
    
    $this->assertEquals(
      array('__construct','getBanane', 'getZeppelin'),
      array_keys($this->classBuilder->getMethods())
    );
  }
  
  /**
   * @depends testAddProperty
   */
  public function testGenerateDocBlocks(ClassBuilder $cb) {
    $this->assertChainable($cb->generateDocBlocks());
    foreach ($cb->getProperties() as $property) {
      $this->assertNotNull($property->getDocBlock());
    }
    
    $this->assertNotNull($cb->getGClass()->getDocBlock());
  }
  
  public function testCreateClassDocBlock_doesNotRecreate() {
    $dbExpected = $this->classBuilder->createClassDocBlock();
    $dbActual = $this->classBuilder->createClassDocBlock();
    $this->assertSame($dbExpected, $dbActual);
  }
  
  public function testConstructWithNotEmptyGClass_doeswhat() {
    // what does it?
  }
  
  public function testGetPropertyIsCaseInsensitiv() {
    $property = $this->classBuilder->addProperty('tEST');
    
    $this->assertSame($property,$this->classBuilder->getProperty('test'));
  }
  
  /**
   * @expectedException Psc\Code\Generate\NoSuchPropertyClassBuilderException
   */
  public function testGetProperty_throwsExc() {
    $this->classBuilder->getProperty('iam not here');
  }
}
?>