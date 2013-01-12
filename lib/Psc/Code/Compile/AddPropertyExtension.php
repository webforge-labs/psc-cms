<?php

namespace Psc\Code\Compile;

use Psc\Code\Generate\GClass;
use Psc\Code\AST;
use Psc\Code\Code;
use Psc\Code\Generate\Expression;
use Psc\Code\Generate\GParameter;
use Psc\Code\Generate\GMethod;
use Psc\Code\Generate\ClassBuilder;
use Psc\Code\Generate\ClassBuilderProperty;
use Psc\Data\Type\Type;
use Psc\Data\Type\ObjectType;
use Psc\A;

/**
 * @TODO der Constructor muss parent::__construct aufrufen, wenn überschrieben,parameter sollten vererbt werden
 * @TODO self::DEPENDENCY_INJECTION_GETTER, 
 */
class AddPropertyExtension extends Extension {
  
  const CONSTRUCTOR_PREPEND = 0;
  const CONSTRUCTOR_APPEND = A::END;
  
  const DEPENDENCY_INJECTION_CONSTRUCTOR = 'constructor';
  const DEPENDENCY_INJECTION_GETTER = 'getter';
  const DEPENDENCY_INJECTION_NONE = FALSE;
  
  /**
   * @var Psc\Code\Generate\ClassBuilderProperty
   */
  protected $cProperty;
  
  /**
   * @var stdClass
   */
  protected $property;
  
  protected $generateSetter = TRUE;
  protected $generateGetter = TRUE;
  
  /**
   * CONSTRUCTOR_*
   * prepend: zum Einfügen als ersten Parameter
   * append. zum Einfügen als letzten Parameter
   * int zum Einfügen an Position x in den Constructor
   * FALSE um nichts in den Constructor einzufügen
   */
  protected $generateInConstructor = FALSE;
  
  protected $constructorDefaultValue = GParameter::UNDEFINED;
  
  
  protected $dependencyInjection = FALSE;
  
  public function __construct($name, Type $type, $dependencyInjection = self::DEPENDENCY_INJECTION_NONE, $upcaseName = NULL) {
    $this->property = (object) compact('name', 'type', 'upcaseName');
    $this->setDependencyInjection($dependencyInjection);
    parent::__construct();
  }
  
  /**
   * Fügt ein Property hinzu
   *
   * added das Property, seine Setter und gegebenfalls auch einen Eintrag im Constructor
   */
  public function compile(GClass $gClass, $flags = 0x000000) {
    // das ist okay, wenn man getters oder setters oder den constructor adden will (imo)
    //if ($gClass->hasProperty($this->property->name)) {
    //  throw new \InvalidArgumentException('Property: '.$this->property->name. ' besteht bereits in Klasse '.$gClass);
    //}
    $this->createClassBuilder($gClass);
    
    $this->cProperty = $this->classBuilder->addProperty($this->property->name, $this->property->type);
    if ($this->property->upcaseName) {
      $this->cProperty->setUpcaseName($this->property->upcaseName);
    }
    
    if ($this->generateSetter) {
      $this->classBuilder->generateSetter($this->cProperty, NULL, ClassBuilder::INHERIT);
    }

    if ($this->generateGetter) {
      $this->classBuilder->generateGetter($this->cProperty, NULL, ClassBuilder::INHERIT);
    }
    
    if (($cs = $this->generateInConstructor) !== FALSE) {
      $constructor = $this->classBuilder->getConstructor(TRUE);
      
      if (!$constructor->hasParameter($this->property->name)) {
        $parameter = new GParameter($this->property->name,
                                    $this->property->type ? $this->property->type->getPHPHint() : NULL,
                                    $this->constructorDefaultValue
                                   );
        $constructor->addParameter($parameter, $this->generateInConstructor);
        
        if ($this->dependencyInjection === self::DEPENDENCY_INJECTION_CONSTRUCTOR) {
          if (!($this->property->type instanceof ObjectType) || !$this->property->type->hasClass()) {
            throw new \InvalidArgumentException('Wenn DependencyInjection CONSTRUCTOR ist, muss der property-type ein ObjectType sein mit einer Klasse. Type ist: '.Code::varInfo($this->property->type));
          }
          
          $objectValue = new Expression(sprintf('$%s ?: new %s()', $parameter->getName(), $this->property->type->getGClass()->getClassName()));
        } else {
          $objectValue = new AST\Variable($parameter->getName(), new AST\LType('Mixed'));
        }
        
        
        $constructor->appendBodyLines(array(
          $this->codeWriter->callSetter(
                                        new AST\Variable('this', new AST\LType('Object')),
                                        $this->cProperty->getUpcaseName(),
                                        $objectValue
                                       ).';'
          )
        );
      }
    }
    
    $this->classBuilder->generateDocBlocks();
    
    return $gClass;
  }
  
  /**
   * @param mixed $generateInConstructor
   * @chainable
   */
  public function setGenerateInConstructor($generateInConstructor) {
    $this->generateInConstructor = $generateInConstructor;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getGenerateInConstructor() {
    return $this->generateInConstructor;
  }


  
  /**
   * @param bool $generateGetter
   * @chainable
   */
  public function setGenerateGetter($generateGetter) {
    $this->generateGetter = $generateGetter;
    return $this;
  }

  /**
   * @return bool
   */
  public function getGenerateGetter() {
    return $this->generateGetter;
  }


  
  /**
   * @param bool $generateSetter
   * @chainable
   */
  public function setGenerateSetter($generateSetter) {
    $this->generateSetter = $generateSetter;
    return $this;
  }

  /**
   * @return bool
   */
  public function getGenerateSetter() {
    return $this->generateSetter;
  }


  /**
   * @param mixed $constructorDefaultValue
   * @chainable
   */
  public function setConstructorDefaultValue($constructorDefaultValue) {
    $this->constructorDefaultValue = $constructorDefaultValue;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getConstructorDefaultValue() {
    return $this->constructorDefaultValue;
  }
  
  /**
   * @param const $dependencyInjection
   * @chainable
   */
  public function setDependencyInjection($dependencyInjection) {
// @TODO self::DEPENDENCY_INJECTION_GETTER, 
    Code::value($dependencyInjection, self::DEPENDENCY_INJECTION_CONSTRUCTOR, self::DEPENDENCY_INJECTION_NONE);
    $this->dependencyInjection = $dependencyInjection;
    return $this;
  }

  /**
   * @return const
   */
  public function getDependencyInjection() {
    return $this->dependencyInjection;
  }


}
?>