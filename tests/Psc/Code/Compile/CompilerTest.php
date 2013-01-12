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
 * @group class:Psc\Code\Compile\Compiler
 */
class CompilerTest extends \Psc\Code\Test\Base {
  
  protected $project;
  
  public function setUp() {
    $this->project = PSC::getProject();
  }
  
  public function testCompileWithNoChanges() {
    $compilerFile = $this->getFile('class.Compiler.php');
    
    require $compilerFile;
    $gClass = new GClass(new ReflectionClass('Psc\Code\Compile\CompilerTestFixture'));
    //$gClass->setSrcFileName((string) $compilerFile);
    
    $compiler = new Compiler(new ClassReader($compilerFile, $gClass), new ClassWriter());
    
    $in = $this->newFile('in.php');
    $in->writeContents($compilerFile->getContents());
    
    $out = $this->newFile('out.php');
    $compiler->compile($out);
    
    $this->assertFileEquals((string) $in, (string) $out);
  }
}
?>