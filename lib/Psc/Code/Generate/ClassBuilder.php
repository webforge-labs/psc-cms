<?php

namespace Psc\Code\Generate;

use Psc\Code\Callback;
use Psc\TPL\TPL;
use Psc\Data\Type\Type;

/**
 * Eine Klasse um einfach eine Klasse von scratch zu erstellen
 * 
 * @TODO wir brauchen auch eine ClassBuilderMethod um so ähnlich wie property kapseln zu können. Es fehlen dann aber Typen für alle Parameter
 * @TODO method-doc-blocks sind nicht automatisch
 */
class ClassBuilder extends \Psc\Object {
  
  const INHERIT = 16;
  
  /**
   * @var Psc\Code\Generate\GClass
   */
  protected $class;
  
  /**
   * @var ClassBuilderProperty[]
   */
  protected $properties = array();
  
  protected $methods = array();
  
  /**
   * @var Psc\Code\Generate\CodeWriter
   */
  protected $codeWriter;
  
  public function __construct(GClass $class, CodeWriter $codeWriter = NULL) {
    $this->class = $class;
    $this->setCodeWriter($codeWriter ?: new CodeWriter());
  }
  
  /**
   * @param string|ClassBuilderProperty $property
   * @return ClassBuilderProperty
   */
  public function addProperty($property, Type $type = NULL) {
    $connect = FALSE;
    if (!($property instanceof ClassBuilderProperty)) {
      $property = new ClassBuilderProperty($property, $this);
      if (isset($type))
        $property->setType($type);
    } else {
      $connect = TRUE;
    }
    
    if (array_key_exists($key = mb_strtolower($property->getName()),$this->properties)) {
      throw new ClassBuilderException('Property '.$property->getName().' existiert bereits');
    }
    
    $this->properties[$key] = $property;
    if ($connect) 
      $this->class->addProperty($property->getGProperty());
    
    return $property;
  }
  
  /**
   * @return GMethod
   */
  public function addMethod(GMethod $method) {
    $this->class->addMethod($method);
    $method->setClassBuilder($this);
    
    return $method;
  }
  
  public function getMethods() {
    return $this->class->getMethods();
  }
  
  public function hasMethod($name) {
    return //array_key_exists(mb_strtolower($name),$this->methods) ||
            $this->class->hasOwnMethod($name) ||
           ($this->class->getParentClass() != NULL && $this->class->getParentClass()->hasMethod($name));
  }
  
  public function getMethod($name) {
    //if (!array_key_exists($key = mb_strtolower($name),$this->methods)) {
      if ($this->class->hasOwnMethod($name)) {
        return $this->class->getMethod($name);
      }
      
      // elevate the method
      if ($this->class->getParentClass() != NULL && $this->class->getParentClass()->hasMethod($name)) {
        $this->addMethod($method = $this->class->getParentClass()->getMethod($name));
        //$method->setWasElevated(TRUE);
      } else {
        throw new NoSuchMethodClassBuilderException(sprintf("Methode '%s' ist nicht vorhanden", $name));
      }
    //}
    
    return $this->class->getMethod($name);
  }
  
  /**
   * Erstellt eine neue Methode und fügt diese der Klasse hinzu
   * 
   * @return GMethod
   */
  public function createMethod($name, $params = array(), $body = NULL, $modifiers = 256) {
    $method = $this->class->createMethod($name, $params, $body, $modifiers);
    $method->setClassBuilder($this);
    return $method;
  }
  
  public function createProtectedMethod($name, $params = array(), $body = NULL, $modifiers = 512) {
    return $this->createMethod($name, $params, $body, $modifiers);
  }
  
  /**
   * Erzeugt für alle Properties einen Getter
   * 
   * existiert der Getter bereits wird er nicht neu erzeugt
   */
  public function generateGetters() {
    foreach ($this->properties as $property) {
      $this->generateGetter($property);
    }
    
    return $this;
  }
  
  /**
   * 
   * existiert der Getter wird er nicht überschrieben
   * @param string $getter der volle Name des Getters ansonsten wir get.ucfirst($property) genommen
   */
  public function generateGetter(ClassBuilderProperty $property, $getter = NULL, $flags = 0) {
    $getter = $getter ?: $property->getGetterName();
    
    // wenn die klasse die Methode wirklich selbst hat, überschreiben wir diesen nicht
    if ($this->class->hasOwnMethod($getter)) {
      return $this;
    }
    
    // die methode gibt es auch nicht in der hierarchy, dann erstelleln wir sie ganz normal
    if (!$this->class->hasMethod($getter)) {
      $gMethod = $this->class->createMethod(
        $getter,
        array(),
        $this->createCode('getter', array(
          'property'=>$property->getName()
        )),
        GMethod::MODIFIER_PUBLIC
      );
      
      $gMethod->createDocBlock()->addSimpleAnnotation('return '.$property->getDocType());
    } elseif (($flags & self::INHERIT)) {
      
      // wir wollen die methode in dieser klasse haben (das besagt das flag), allerdings müssen wird ie methode ableiten
      $method = clone $this->class->getMethod($getter);
    
      if ($method->isAbstract()) {
        $method->setAbstract(FALSE);
      }
      
      // check ob das property->getName() richtig ist?
      $method->setBodyCode(
        $this->createCode('getter', array(
          'property'=>$property->getName()
        ))
      );
      $this->addMethod($method);
    }
    
    return $this;
  }
  
  /**
   * Erzeugt für alle Properties einen Getter
   * 
   * existiert der Setter bereits wird er nicht neu erzeugt
   */
  public function generateSetters() {
    foreach ($this->properties as $property) {
      $this->generateSetter($property);
    }
    
    return $this;
  }
  
  /**
   * Fügt dem Constructor Zeilen von Code hinzu
   * 
   * wenn der Constructor nicht existiert, wird er erstellt
   */
  public function appendConstructorBody(Array $lines) {
    $this->getConstructor(TRUE)->appendBodyLines($lines);
    
    return $this;
  }
  
  public function getConstructor($create = true) {
    $constructor = NULL;
    if (!$this->class->hasMethod('__construct')) {
      $constructor = $this->class->createMethod('__construct', array(), array());
      $this->class->setMethodOrder($constructor, GClass::PREPEND);
    }
    
    return $constructor ?: $this->class->getMethod('__construct');
  }
  
  /**
   * @param string der volle Name des Setters
   * 
   * @param $flags wenn dies z.B: INHERIT ist wird immer eine eigene Methode für den Setter erstellt (auch wenn es die Methode schon in der Hierarchy gibt)
   */
  public function generateSetter(ClassBuilderProperty $property, $setter = NULL, $flags = 0) {
    $setter = $setter ?: $property->getSetterName();
    
    // wenn die klasse die Methode wirklich selbst hat, überschreiben wir diesen nicht
    if ($this->class->hasOwnMethod($setter)) {
      return $this;
    }
    
    // die methode gibt es auch nicht in der hierarchy, dann erstelleln wir sie ganz normal
    if (!$this->class->hasMethod($setter)) {
      $gMethod = $this->class->createMethod(
        $setter,
        array(
         new GParameter(
           $property->getName(),
           $property->getPHPHint(),
           ($property->isNullable() && $property->getPHPHint() != NULL) ? NULL : GParameter::UNDEFINED
         )
        ),
        $this->createCode('setter', array(
          'property'=>$property->getName()
        )),
        GMethod::MODIFIER_PUBLIC
      );
      
      $gMethod->createDocBlock()->addSimpleAnnotation('param '.($property->getDocType() ?: 'undefined').' $'.$property->getName());
    } elseif (($flags & self::INHERIT)) {
      // wir wollen die methode in dieser klasse haben (das besagt das flag), allerdings müssen wird ie methode ableiten
      $method = clone $this->class->getMethod($setter);
    
      if ($method->isAbstract()) {
        $method->setAbstract(FALSE);
      }
      
      // check ob das property->getName() richtig ist?
      if (count($method->getParameters()) === 0) {
        $method->addParameter(
          new GParameter(
            $property->getName(),
            $property->getPHPHint()
          )
        );
      } elseif (count($method->getParameters()) > 0) {
        $method->getParameterByIndex(0)->setName($property->getName());
      }
      
      $method->setBodyCode(
        $this->createCode('setter', array(
          'property'=>$property->getName()
        ))
      );
      $this->addMethod($method);
    }
    return $this;
  }
  
  public function getSetter($property) {
    if (is_string($property)) {
      $property = $this->getProperty($property);
    }
    
    return $this->class->getMethod($property->getSetterName());
  }
  
  public function generatePropertiesConstructor(Array $properties) {
    if ($this->class->hasOwnMethod('__construct')) {
      $constructor = $this->class->getMethod('__construct');
      
      //if (count($constructor->getBodyCode()) > 0) {
      //  throw new \Psc\Exception('Ein constructor für '.$this->class->getFQN().' ist bereits definiert und hat einen Body. Deshalb kann er nicht überschrieben werden'); // ändere dies, falls das mal anders sein soll - dunno (yagni)
      //}
    } else {
      $constructor = $this->class->createMethod('__construct');
      $this->class->setMethodOrder($constructor, GClass::PREPEND);
    }
    
    $code = array();
    
    foreach ($properties as $property) {
      if (is_array($property) && array_key_exists('property',$property)) {
        $argument = $property;
        $property = $argument['property'];
      } else {
        $argument = array();
      }
      
      if (!($property instanceof ClassBuilderProperty))
        throw new \InvalidArgumentException('Es können nur ClassBuilderProperties übergeben werden');
      
      $param = new GParameter($property->getName(),$property->getPHPHint());
      if (array_key_exists('default',$argument)) {
        $param->setDefault($argument['default']);
        $codeName = 'constructorSetterPartOptional';
      } else {
        $codeName = 'constructorSetterPart';
      }
        
      $code = array_merge($code, $this->createCode($codeName, array('setter'=>$property->getSetterName(), 'property'=>$property->getName())));
      
      $constructor->addParameter($param);
    }
    
    $constructor->appendBodyLines($code);
    
    return $this;
  }
  
  public function generateDocBlocks() {
    foreach ($this->properties as $property) {
      $property->createDocBlock(); // erzeugt keinen wenn es schon einen gibt
    }
    
    // @TODO method
    //foreach ($this->methods as $method) {
    //}
    
    $this->createClassDocBlock();
    
    return $this;
  }
  
  public function createClassDocBlock() {
    if (!$this->class->hasDocBlock()) {
      $docBlock = $this->class->createDocBlock();
    } else {
      $docBlock = $this->class->getDocBlock();
    }
    
    return $docBlock;
  }
  
  public function getClassDocBlock() {
    return $this->class->getDocBlock();
  }
  
  /**
   * @return array
   */
  protected function createCode($type, Array $vars) {
    if ($type === 'getter') {
      $code = array(
        'return $this->%property%;'
      );
    } elseif ($type === 'setter') {
      $code = array(
        '$this->%property% = $%property%;',
        'return $this;'
      );
    } elseif ($type === 'constructorSetterPart') {
      $code = array(
        '$this->%setter%($%property%);'
      );
    } elseif ($type === 'constructorSetterPartOptional') {
      $code = array(
        'if (isset($%property%)) {',
        '  $this->%setter%($%property%);',
        '}'
      );
    }
    
    return explode("\n",TPL::miniTemplate(implode("\n",$code), $vars));
  }
  
  public function getProperty($name) {
    if (!array_key_exists($key = mb_strtolower($name),$this->properties)) {
      
      // elevate
      if ($this->class->hasProperty($name)) {
        $this->addProperty($property = new ClassBuilderProperty($name, $this, $this->class->getProperty($name)));
        $property->setWasElevated(TRUE);
      } else {
        throw new NoSuchPropertyClassBuilderException(sprintf(" Property '%s' ist nicht vorhanden",$name));
      }
    }
    
    return $this->properties[$key];
  }
  
  public function hasProperty($name) {
    return array_key_exists(mb_strtolower($name),$this->properties) || $this->class->hasProperty($name);
  }
  
  public function getProperties() {
    return $this->properties;
  }
  
  /**
   * Überschreibt die Properties im ClassBuilder
   */
  public function setProperties(Array $properties) {
    $this->properties = array();
    foreach ($properties as $property) {
      $this->addProperty($property);
    }
    return $this;
  }
  
  public function getGClass() {
    return $this->class;
  }
  
  /**
   * Elevated die ParentClass um properties der Hierarchy laden zu können
   */
  public function setParentClass(GClass $gClass) {
    $this->class->setParentClass($gClass);
    if ($gClass->exists()) {
      try {
        // damit die gclass vernünftig initialisiert wird
        $this->class->elevateParent();
        
      } catch (\Psc\Code\Generate\ReflectionException $e) {
        throw new \Psc\Exception(
          'Die Parent-Klasse: '.$gClass->getFQN().' kann nicht elevated werden. Das ist schlecht, denn so können nicht alle methoden korrekt vererbt werden oder properties erstellt werden.'.
          'Die SyntaxFehler der Klasse müssen zuerst behoben werden',
          0,
          $e
        );
      }
    }
    
    return $this;
  }
  
  /**
   * @param Psc\Code\Generate\CodeWriter $codeWriter
   */
  public function setCodeWriter(CodeWriter $codeWriter) {
    $this->codeWriter = $codeWriter;
    return $this;
  }
  
  /**
   * @return Psc\Code\Generate\CodeWriter
   */
  public function getCodeWriter() {
    return $this->codeWriter;
  }
}
?>