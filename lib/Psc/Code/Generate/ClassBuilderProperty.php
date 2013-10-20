<?php

namespace Psc\Code\Generate;

use Webforge\Types\Type;
use InvalidArgumentException;
use Psc\Code\Annotation;

/**
 * 
 */
class ClassBuilderProperty extends \Psc\Object {
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * Der Name in Camel-Up-Case
   * 
   * Kann gesetzt werden wenn dies nicht ucfirst($name) sein soll
   * z.b. HTML für html damit die getter/setter getHTML und setHTML heißen
   * @var string
   */
  protected $upcaseName;
  
  /**
   * @var Webforge\Types\Type
   */
  protected $type;
  
  /**
   * @var ClassBuilder
   */
  protected $builder;
  
  /**
   * @var Psc\Code\Generate\GProperty
   */
  protected $gProperty;
  
  /**
   * @var bool
   */
  protected $wasElevated = false;
  
  /**
   * For Example property with type hint as an PHP Class nullable can be true to allow to set the value to a undefined state in the setter
   * @var bool
   */
  protected $nullable = FALSE;
  
  public function __construct($name, ClassBuilder $classBuilder, GProperty $gProperty = NULL, $nullable = FALSE) {
    $this->setName($name);
    $this->builder = $classBuilder;
    $this->gProperty = $gProperty ?: $this->builder->getGClass()->createProperty($name);
    $this->setNullable($nullable);
  }
  
  /**
   * @param string $name erster Buchstabe muss Alphabetisch sein
   * @chainable
   */
  public function setName($name) {
    if (!is_string($name)) throw new InvalidArgumentException('Name muss ein String sein');
    // wir sind hier more strict als php: 127-255 sind nicht erlaubt
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/',$name)) throw new InvalidArgumentException('Name muss mit Alpha oder Underscore beginnen');
    
    $this->name = $name;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * @return string
   */
  public function getGetterName() {
    return 'get'.$this->getUpcaseName();
  }
  
  /**
   * @return string
   */
  public function getSetterName() {
    return 'set'.$this->getUpcaseName();
  }
  
  public function getUpcaseName() {
    return $this->upcaseName ?: ucfirst($this->name);
  }
  
  /**
   * 
   * dieser string sollte für @param und @var benutzt werden
   * @return string|NULL
   */
  public function getDocType() {
    return isset($this->type) ? $this->type->getDocType() : NULL;
  }
  
  /**
   * Der Hint für einen GParameter der mit diesem Property bestückt werden soll
   * 
   * @return string|NULL (glaub ich)
   */
  public function getPHPHint() {
    return isset($this->type) ? $this->type->getPHPHint() : NULL;
  }
  
  /**
   * @param Webforge\Types\Type $type
   * @chainable
   */
  public function setType(Type $type) {
    $this->type = $type;
    return $this;
  }
  
  /**
   * @return Webforge\Types\Type
   */
  public function getType() {
    return $this->type;
  }
  
  /**
   * 
   * Hilfsfunktion zum ermöglichen von geilen chainables:
   * 
   *  $cb = new ClassBuilder();
   *  $cb->addProperty('eins')->setType('MyType1')->addProperty('zwei'->setType('MyType2')
   * 
   * usw
   */
  public function addProperty($name) {
    return $this->builder->addProperty($name);
  }
  
  public function getClassBuilder() {
    return $this->builder;
  }
  
  /**
   * kann gesetzt werden, wenn man z.B. statt
   * setHtml setHTML erzeugen will
   */
  public function setUpcaseName($name) {
    $this->upcaseName = $name;
    return $this;
  }
  
  public function getGPropery() {
    return $this->gProperty;
  }
  
  /**
   * @return Psc\Generate\DocBlock
   */
  public function createDocBlock($body = NULL) {
    if (!$this->gProperty->hasDocBlock())
      $docBlock = $this->gProperty->createDocBlock($body);
    else
      $docBlock = $this->gProperty->getDocBlock();
    
    if ($this->type != NULL && !$docBlock->hasSimpleAnnotation('var')) {
      $docBlock->addSimpleAnnotation('var '.($this->getDocType() ?: 'undefined'));
    }
    
    return $docBlock;
  }
  
  /**
   * @return Psc\Generate\DocBlock|NULL
   */
  public function getDocBlock() {
    return $this->gProperty->getDocBlock();
  }
  
  /**
   * @chainable
   */
  public function addAnnotation(Annotation $annotation) {
    $this->getDocBlock()->addAnnotation($annotation);
    return $this;
  }
  
  /**
   * @TODO wie übergibt man hier klassen konstanten?
   * @param mixed $value
   */
  public function setDefaultValue($value) {
    $this->gProperty->setDefaultValue($value);
    return $this;
  }
  
  /**
   * Setzt ob ide Klasse aus der ParentKlasse hinzugefügt wurde
   * 
   * @param bool $wasElevated
   * @chainable
   */
  public function setWasElevated($wasElevated) {
    $this->wasElevated = $wasElevated;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function getWasElevated() {
    return $this->wasElevated;
  }
  
  /**
   * @param bool $nullable
   */
  public function setNullable($nullable) {
    $this->nullable = $nullable;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function isNullable() {
    return $this->nullable;
  }
}
?>