<?php

namespace Psc\Code\Compile;

use Psc\Code\Generate\GClass;

/**
 * @group class:Psc\Code\Compile\MarkCompiledExtension
 */
class MarkCompiledExtensionTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Compile\MarkCompiledExtension';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $gClass = new GClass('NotMarkedClass');
    
    $ext = new MarkCompiledExtension();
    $this->assertInstanceof('Psc\Code\Compile\ClassCompiler', $ext);
    $ext->compile($gClass);
    
    // jaja
    $this->assertTrue($gClass->getDocBlock()->hasAnnotation('Psc\Code\Compile\Annotations\Compiled'));
  }
  
  public function testAlreadyMarkedClassGetsNotMarked() {
    $gClass = new GClass('MarkedClass');
    $gClass->createDocBlock()->addAnnotation(\Psc\Code\Compile\Annotations\Compiled::create());
    
    // erstellen hier darf jetzt keine neue annotation in out erstellen
    $ext = new MarkCompiledExtension();
    $ext->compile($gClass);
    $this->assertCount(1, $gClass->getDocBlock()->getAnnotations('Psc\Code\Compile\Annotations\Compiled'), 'Anzahl der @Compiled Annotations ist falsch!');
  }
}
?>