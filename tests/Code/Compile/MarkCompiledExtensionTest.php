<?php

namespace Psc\Code\Compile;

use Psc\Code\Generate\GClass;

class MarkCompiledExtensionTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Compile\MarkCompiledExtension';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $inClass = new GClass('NotMarkedClass');
    $outClass = new GClass('MarkedClass');
    
    $ext = $this->createMarkCompiledExtension();
    $this->assertInstanceof('Psc\Code\Compile\ClassCompiler', $ext);
    $ext->compile($inClass, $outClass);
    
    // jaja
    $this->assertTrue($outClass->getDocBlock()->hasAnnotation('Psc\Code\Compile\Annotations\Compiled'));
    
    // erstellen hier darf jetzt keine neue annotation in out erstellen
    $inClass->createDocBlock()->addAnnotation(\Psc\Code\Compile\Annotations\Compiled::create());
    $ext->compile($inClass, $outClass);
    $this->assertCount(1, $outClass->getDocBlock()->getAnnotations('Psc\Code\Compile\Annotations\Compiled'), 'Anzahl der @Compiled Annotations ist falsch!');
  }
  
  public function createMarkCompiledExtension() {
    return new MarkCompiledExtension();
  }
}
?>