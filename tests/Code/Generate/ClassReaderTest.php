<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\ClassReader;
use Psc\PSC;

class ClassReaderTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Generate\ClassReader';
    parent::setUp();
    $this->classReader = $this->createClassReader('u1');
  }
  
  public function testConstruct() {
    $this->assertChainable($this->classReader);
  }
  
  public function testGetClass() {
    $classReader = new ClassReader(PSC::getProject()->getClassFile('Psc\Code\Generate\ClassReader'));
    // wir testen ob der ClassReader die GClass korrekt setzt, wenn wir nur die Datei übergeben
    $this->assertInstanceof('Psc\Code\Generate\GClass',$gClass = $classReader->getClass());
    $this->assertEquals('Psc\Code\Generate\ClassReader', $gClass->getFQN());
  }

  public function testGetReflectionClass() {
    $this->assertInstanceof('ReflectionClass',$this->classReader->getReflectionClass());
  }
  
  public function testReadUseStatements() {
    $this->assertEquals(array('GClass'=>new GClass('Psc\Code\Generate\GClass'),
                              'Code'=>new GClass('Psc\Code\Code'),
                              's'=>new GClass('Psc\String'),
                              'DataInput'=>new GClass('Psc\DataInput'),
                              'doctrinehelper'=>new GClass('Psc\Doctrine\Helper')
                              ),
                        $this->classReader->readUseStatements()
                       );
    // eigentlich will ich hier S bei Alias von String groß haben (und auch DoctrineHelper)!
  }
  
  public function createClassReader($fixture = 'u1') {
    $file = $this->getFile('fixture.TestClass.'.$fixture.'.php');
    require_once $file;
    return new ClassReader($file, new GClass('Psc\Code\Generate\Fixtures\TestClass1'));
  }
}
?>