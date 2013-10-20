<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\ClassBuilder;
use Psc\Code\Callback;
use Webforge\Types\Type;
use Webforge\Types\MarkupTextType;
use Webforge\Types\ImageType;

/**
 * @group generate
 * @group class:Psc\Code\Generate\ClassBuilder
 * @group entity-building
 */
class ClassBuilderWithPropertyTest extends \Psc\Code\Test\Base {

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
         ->setType(new ImageType())
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
/**
 * @return string
 */
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
/**
 * @param string $thumb
 */
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
    $this->assertEquals('Webforge\Types\Interfaces\Link',$p3->getHint()->getFQN());
    
    $constructorPHP = <<< 'PHP'
  $this->setImage($image);
  $this->setText($text);
  $this->setLink($link);

PHP;
    
    $this->assertEquals($constructorPHP, $m->getBody());
    //print $cb->getGClass()->php();
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