<?php

namespace Psc\Code\Generate;

use \Reflector,
    \Webforge\Common\ArrayUtil AS A,
    \ReflectionMethod,
    \Webforge\Common\String AS S,
    \Psc\Code\Code
;

/**
 * @TODO Test DocBlock
 */
class GMethod extends GFunction {
  
  const MODIFIER_STATIC = ReflectionMethod::IS_STATIC;
  const MODIFIER_PUBLIC = ReflectionMethod::IS_PUBLIC;
  const MODIFIER_PROTECTED = ReflectionMethod::IS_PROTECTED;
  const MODIFIER_PRIVATE = ReflectionMethod::IS_PRIVATE;
  const MODIFIER_ABSTRACT = ReflectionMethod::IS_ABSTRACT;
  const MODIFIER_FINAL = ReflectionMethod::IS_FINAL;
  
  protected $modifiers = self::MODIFIER_PUBLIC;

  /**
   * @var GClass
   */
  protected $declaringClass;

  /**
   * @var Psc\Code\GenerateClassBuilder
   */
  protected $classBuilder;
  
  /**
   * @param string|array body
   */
  public function __construct($name = NULL, Array $parameters = array(), $body = NULL, $modifiers = self::MODIFIER_PUBLIC) {
    parent::__construct($name, $parameters, $body);
    $this->modifiers = $modifiers;
  }
  
  /**
   * Gibt den PHP Code für die Methode zurück
   *
   * Nach der } ist kein LF
   */
  public function php($baseIndent = 0) {
    $cr = "\n";
    
    $php = $this->phpDocBlock($baseIndent);
    
    // vor die modifier muss das indent
    $php .= str_repeat(' ',$baseIndent);
    
    $ms = array(self::MODIFIER_ABSTRACT => 'abstract',
                self::MODIFIER_PUBLIC => 'public',
                self::MODIFIER_PRIVATE => 'private',
                self::MODIFIER_PROTECTED => 'protected',
                self::MODIFIER_STATIC => 'static',
                self::MODIFIER_FINAL => 'final'
               );
    
    foreach ($ms as $const => $modifier) {
      if (($const & $this->modifiers) == $const)
        $php .= $modifier.' ';
    }
    
    $php .= parent::php($baseIndent); 
    
    return $php;
  }
  
  public function phpSignature($baseIndent = 0) {
    return parent::phpSignature(0); // damit hier vor function nicht indent eingefügt wird (der muss vor unsere modifier)
  }
  
  public function phpBody($baseIndent = 0) {
    if ($this->isAbstract()) {
      return ';';
    } else {
      return parent::phpBody($baseIndent);
    }
  }
  
  public function elevate(Reflector $reflector) {
    parent::elevate($reflector);
    
    $this->elevateValues(
      array('modifiers','getModifiers')
    );
    
    $this->elevateDocBlock($reflector);
    $this->declaringClass = new GClassReference($reflector->getDeclaringClass()->getName()); // gibt auch zu deep rekursion
    
    return $this;
  }
  
  public function isStatic() {
    return ($this->modifiers & self::MODIFIER_STATIC) == self::MODIFIER_STATIC;
  }

  public function isPublic() {
    return ($this->modifiers & self::MODIFIER_PUBLIC) == self::MODIFIER_PUBLIC;
  }

  public function isProtected() {
    return ($this->modifiers & self::MODIFIER_PROTECTED) == self::MODIFIER_PROTECTED;
  }

  public function isPrivate() {
    return ($this->modifiers & self::MODIFIER_PRIVATE) == self::MODIFIER_PRIVATE;
  }

  public function isAbstract() {
    return ($this->modifiers & self::MODIFIER_ABSTRACT) == self::MODIFIER_ABSTRACT;
  }

  public function isFinal() {
    return ($this->modifiers & self::MODIFIER_FINAL) == self::MODIFIER_FINAL;
  }
  
  public function setAbstract($bool) {
    return $this->setModifier(self::MODIFIER_ABSTRACT,$bool);
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