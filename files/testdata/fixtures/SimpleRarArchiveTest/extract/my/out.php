<?php

namespace Psc\Code\Compile;

use Psc\Code\Generate\GClass,
    Psc\Code\Generate\ClassWriter,
    Psc\System\File,
    Psc\Code\Compile\Extension;

class Compiler extends Psc\Object {

  protected $classWriter = NULL;

  protected $gClass = NULL;

  protected $cClass = NULL;

  protected $extensions = array (
);

  public function __construct(Psc\Code\Generate\GClass $gClass, Psc\Code\Generate\ClassWriter $classWriter = NULL) {
    $this->classWriter = $classWriter ?: new ClassWriter();
    $this->gClass = $gClass;
    
    $this->setUp();
  }

  public function setUpDefaultExtensions() {
    //$this->addCompiledAnnotation();
  
  }

  public function compile(Psc\System\File $out, $flags = 0) {
    
    $this->cClass = clone $this->gClass; // mit leerer oder mit clone anfangen?
    
    $this->processExtensions();
    
    $this->classWriter->setClass($this->cClass);
    $this->classWriter->write($out, array(), ClassWriter::OVERWRITE);
    
    $this->syntaxCheck($out);
    
    return $this;
  }

  protected function processExtensions($flags = 0) {
    foreach ($this->extensions as $extension) {
    
      $extension->compile($this->gClass, $this->cClass, $flags);
    
    }
  }

  protected function syntaxCheck(Psc\System\File $classFile) {
    //@TODO
  
  }

  public function setUp() {
    $this->setUpDefaultExtensions();
  }

  public function addExtension(Psc\Code\Compile\Extension $extension) {
    $this->extensions[] = $extension;
    return $this;
  }

  public function getExtensions() {
    return $this->extensions;
  }
}
?>