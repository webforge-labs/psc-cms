<?php

namespace Psc\Code\Compile;

use Psc\Code\Compile\Compiler;
use Psc\Code\Generate\ClassWriter;
use Psc\Code\Generate\ClassReader;
use Psc\Code\Generate\GClass;
use ReflectionClass;
use Psc\PSC;

/**
 * @group compile
 */
class CompilerTest extends \Psc\Code\Test\Base {
  
  protected $project;
  
  public function setUp() {
    $this->project = PSC::getProject();
  }
  
  public function testCompileWithNoChanges() {
    $gClass = new GClass(new ReflectionClass('Psc\Code\Compile\Compiler'));
    $compilerFile = $this->getFile('class.Compiler.php');
    $compiler = new Compiler(new ClassReader($compilerFile, $gClass), new ClassWriter());
    
    $in = $this->newFile('in.php');
    $in->writeContents($compilerFile->getContents());
      
    $out = $this->newFile('out.php');
    $compiler->compile($out);
    
    $this->assertFileEquals((string) $in, (string) $out);
  }
}
?>