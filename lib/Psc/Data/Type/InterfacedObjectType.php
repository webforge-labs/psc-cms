<?php

namespace Psc\Data\Type;

use Psc\Code\Generate\GClass;

abstract class InterfacedObjectType extends \Psc\Data\Type\ObjectType implements InterfacedType {

  public function setClass(GClass $class = NULL) {
    if ($class === NULL) {
      throw new TypeException('Für InterfacedObjectType kann Parameter 1 von setClass nicht NULL sein');
    }
    
    try {
      // das ist schneller als gClass::hasInterface
      if ($class->getReflection()->implementsInterface($this->getInterface())) {
        return parent::setClass($class);
      }
      
    } catch (\ReflectionException $e) { }
      
    throw new TypeException(
      sprintf("Die Klasse '%s' für den InterfacedObjectType muss das Interface: '%s' implementieren.", $class->getFQN(),$this->getInterface())
    );
  }
  
  public function getPHPHint($namespaceContext = NULL) {
    return $this->getInterface(); // @TODO this fails to respect namespaceContext
  }
}
?>