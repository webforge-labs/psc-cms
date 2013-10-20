<?php

namespace Psc\Code\Compile;

use Psc\Code\Generate\ClassBuilder;
use Psc\Code\Generate\GClass;
use Psc\Code\Generate\CodeWriter;
use Webforge\Types\Type;

abstract class Extension implements ClassCompiler {

  /**
   * Muss selbst erstellt werden
   * 
   * @var Psc\Code\Generate\ClassBuilder
   */
  protected $classBuilder;
  
  /**
   * @var Psc\Code\Generate\CodeWriter
   */
  protected $codeWriter;
  
  public function __construct(CodeWriter $codeWriter = NULL) {
    $this->codeWriter = $codeWriter ?: new CodeWriter();
  }
  
  protected function after($extensionName) {
    $this->needsAfter = $extensionName;
    return $this;
  }

  protected function before($extensionName) {
    $this->needsBefore = $extensionName;
    return $this;
  }

  /**
   * @return Psc\Code\Generate\ClassBuilder
   */
  public function createClassBuilder(GClass $gClass) {
    $this->classBuilder = new ClassBuilder($gClass);
    return $this->classBuilder;
  }
  
  /**
   * @return ClassBuilderProperty
   */
  public function createPropertyIfNotExists($propertyName, Type $type = NULL) {
    if ($this->classBuilder->hasProperty($propertyName)) {
      return $this->classBuilder->getProperty($propertyName);
    } else {
      return $this->classBuilder->addProperty($propertyName, $type);
    }
  }
  
  /**
   * Gibt einen Array von Extensions Namen zurück, die ausgeführt werden müssen nachdem die Extension ausgeführt wird
   */
  public function needsAfter() {
    return $this->needsAfter;
  }
  
  /**
   * Gibt einen Array von Extensions Namen zurück, die ausgeführt werden müssen bevor die Extension ausgeführt wird
   */
  public function needsBefore() {
    return $this->needsBefore;
  }

  /**
   * @param ClassBuilder $classBuilder
   * @chainable
   */
  public function setClassBuilder(ClassBuilder $classBuilder) {
    $this->classBuilder = $classBuilder;
    return $this;
  }

  /**
   * @return ClassBuilder
   */
  public function getClassBuilder() {
    return $this->classBuilder;
  }
}
?>