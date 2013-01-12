<?php

namespace Psc\Code\Generate;

use Reflector;
use ReflectionProperty;

class GProperty extends GObject {
  
  const MODIFIER_STATIC = ReflectionProperty::IS_STATIC;
  const MODIFIER_PUBLIC = ReflectionProperty::IS_PUBLIC;
  const MODIFIER_PROTECTED = ReflectionProperty::IS_PROTECTED;
  const MODIFIER_PRIVATE = ReflectionProperty::IS_PRIVATE;
  
  /**
   * @var bitmap
   */
  protected $modifiers;

  /**
   * @var GClass
   */
  protected $gClass;
  
  /**
   * @var string
   */
  protected $name;

  /**
   * @var GClass
   */
  protected $declaringClass;
  
  public function __construct(GClass $gClass) {
    $this->gClass = $gClass;
  }
  
  public function elevate(Reflector $reflector) {
    $this->reflector = $reflector;
    $this->elevateValues(
      array('modifiers','getModifiers'),
      array('name','getName')
    );
    
    $this->elevateDocBlock($reflector);
    $this->declaringClass = new GClassReference($this->reflector->getDeclaringClass()->getName()); // wird durch gClass ersetzt zu GClass
    
    return $this;
  }
  
  /**
   * Gibt den PHP Code für das Property zurück
   *
   * Nach dem ; ist kein LF
   */
  public function php($baseIndent = 0) {
    $php = $this->phpDocBlock($baseIndent);
    
    $php .= str_repeat(' ',$baseIndent);
    $ms = array(
                self::MODIFIER_PUBLIC => 'public',
                self::MODIFIER_PRIVATE => 'private',
                self::MODIFIER_PROTECTED => 'protected',
                self::MODIFIER_STATIC => 'static',
                // self::MODIFIER_FINAL => 'final' // kein final für properties?
               );
    
    foreach ($ms as $const => $modifier) {
      if (($const & $this->modifiers) == $const)
        $php .= $modifier.' ';
    }
    
    $php .= '$'.$this->name;
    
    if ($this->hasDefaultValue() && $this->getDefaultValue() !== NULL) {
      $php .= ' = '.$this->exportPropertyValue($this->getDefaultValue());
    }
    
    return $php;
  }
  
  /**
   * @return mixed
   */
  public function getDefaultValue() {
    return $this->gClass->getDefaultValue($this);
  }
  
  /**
   * @return bool
   */
  public function hasDefaultValue() {
    return $this->gClass->hasDefaultValue($this);
  }
  
  /**
   * Setzt die Default Value des Properties in der Klasse
   *
   * muss deshalb einer zugeordnet Klasse sein ($this->getGClass())
   * @param mixed $default
   */
  public function setDefaultValue($default) {
    $this->gClass->setDefaultValue($this,$default); // muss das hier getDeclaringClass sein?
    return $this;
  }
  
  /**
   * Entfernt die Default Value aus der Klasse
   *
   * dies ist nicht dasselbe wie $this->setDefaultValue(NULL) !
   */
  public function removeDefaultValue() {
    $this->gClass->removeDefaultValue($this);
    return $this;
  }

  /**
   * Erstellt ein neues GProperty
   *
   * der zweite Parameter ist wichtig
   * 
   * @param Reflector $reflector
   * @param GClass $gClass
   */
  public static function reflectorFactory(Reflector $reflector) {
    $args = func_get_args();
    $g = new static($args[1]);
    $g->elevate($args[0]);
    return $g;
  }
  
  /**
   * @return bool
   */
  public function isStatic() {
    return ($this->modifiers & self::MODIFIER_STATIC) == self::MODIFIER_STATIC;
  }

  /**
   * @return bool
   */
  public function isPublic() {
    return ($this->modifiers & self::MODIFIER_PUBLIC) == self::MODIFIER_PUBLIC;
  }

  /**
   * @return bool
   */
  public function isProtected() {
    return ($this->modifiers & self::MODIFIER_PROTECTED) == self::MODIFIER_PROTECTED;
  }

  /**
   * @return bool
   */
  public function isPrivate() {
    return ($this->modifiers & self::MODIFIER_PRIVATE) == self::MODIFIER_PRIVATE;
  }
  
  /**
   * @return bool
   */
  public function isDefault() {
    return $this->hasDefaultValue();
  }
  
  /**
   * @param GClass $declaringClass
   * @chainable
   */
  public function setDeclaringClass(GClass $declaringClass) {
    $this->declaringClass = $declaringClass;
    return $this;
  }

  /**
   * @return GClass
   */
  public function getDeclaringClass() {
    return $this->declaringClass;
  }
  
  /**
   * @param string $name
   * @chainable
   */
  public function setName($name) {
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
   * @param GClass $gClass
   * @chainable
   */
  public function setGClass(GClass $gClass) {
    $this->gClass = $gClass;
    return $this;
  }

  /**
   * @return GClass
   */
  public function getGClass() {
    return $this->gClass;
  }
}
?>