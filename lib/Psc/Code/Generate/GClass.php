<?php

namespace Psc\Code\Generate;

use ReflectionClass;
use ReflectionMethod;
use Reflector;
use Webforge\Common\ArrayUtil AS A;
use Webforge\Common\String as S;
use Psc\Code\Code;

/**
 * Jede Klasse hat eine Funktion elevate() die modifizierbare parameter einer ReflectionClass auf die G* Klassen anhebt
 *
 * z. B. elevate von GClass nimmt alle Methoden der ReflectionClass und "hebt" diese auf GMethods.
 * GMethod kann dann modifiziert werden (addParameter), etc
 *
 * @TODO wie is mit isInterface() also interfaces schreiben?
 * @TODO bei extends ist manchmal der Gimbel drin (siehe exceptions)
 * 
 * @TODO FIXME! DocBlock Parsing geht nicht richtig. Es muss eigentlich der DocBlock mit allen Annotations ausgelesen werden - ich meine alle alle alle
 *    das ist aber gar nicht so einfach, weil wir ja auch "fremde" annotations haben können. Wir müssen irgendwie im DocBlock speichern, welche Annotations er "geparsed" hat damit hasAnnotation() Sinn macht. (keine => immer Exception, ein paar => exception für die, die er nicht geparsed hat, usw)
 *   D. h. wir brauchen aber eine GClass die DocBlocks geparsed hat. also new GClass() -- ab geht er, ist dann nicht mehr so einfach. Vll eher parsedGClass oder so? Ich glaube man hat eh bald ein Problem immer zwischen elevated und nicht
 * @TODO factory() macht nicht klar, dass da durch die GClass elevated wird!
 * @TODO Static Properties
 */
class GClass extends GObject implements \Webforge\Common\ClassInterface {
  
  const MODIFIER_ABSTRACT = ReflectionClass::IS_EXPLICIT_ABSTRACT;
  const MODIFIER_FINAL= ReflectionClass::IS_FINAL;
  
  const PREPEND = 0;
  const APPEND = A::END;
  
  
  const WITHOUT_CONSTRUCTOR = TRUE;
  
  /**
   * @var string
   */
  protected $name;

  /**
   * @var string
   */
  protected $namespace;
  
  /**
   * @var bitmap
   */
  protected $modifiers;
  
  /**
   * @var GClass[]
   */
  protected $interfaces = array();
  
  /**
   * @var GClass
   */
  protected $parentClass;
  
  /**
   * @var GMethod[]
   */
  protected $methods = array();
  protected $methodsOrder = array();
  
  /**
   * @var GProperty[]
   */
  protected $properties = array();

  /**
   * @var GConstant[]
   */
  protected $constants = array();
  
  /**
   * @var mixed[]
   */
  protected $defaultProperties = array();
  
  protected $startLine;
  protected $endLine;
  
  /**
   * @var string
   */
  protected $srcFileName;
  
  /**
   * Alle Klassen die nicht in Methoden-Argumenten oder extends oder ähnlichem vorkommen
   *
   * Methoden, die dynamischen Code enthalten können z. B. usedClasses hinzufügen, damit dies der ClassWriter extrahieren kann
   * @var array
   */
  protected $usedClasses = array();
  
  
  /**
   * @var object
   */
  protected $prototype;
  
  /**
   *
   * Wenn ein string übergeben wird, wird nie geprüft ob es die Klasse gibt sondern direkt ein Objekt mit
   * der neuen klasse angelegt
   * 
   * @param string|ReflectionClass $class FQN der Klassenname oder eine ReflectionClass von der analysiert werden soll, kann leer sein aum ein leeres Objekt zu erstellen
   */
  public function __construct($class = NULL)  {
    parent::__construct();
    if ($class instanceof ReflectionClass) {
      $this->elevate($class);
    } elseif ($class instanceof GClass) {
      $this->setName($class->getFQN());
    } elseif (is_string($class)) {
      $this->setName($class);
    }
  }
  
  /**
   * @return Object<{$this->getFQN()}>
   */
  public static function newClassInstance($class, Array $params = array()) {
    if ($class instanceof GClass) {
      return $class->newInstance($params);
      //$refl = new ReflectionClass($class->getFQN());
      
    } elseif ($class instanceof ReflectionClass) {
      $refl = $class;
    } elseif (is_string($class)) {
      $refl = new ReflectionClass($class);
    } else {
      throw new InvalidArgumentException('class kann nur string,gclass oder reflectionclass sein');
    }
    
    return $refl->newInstanceArgs($params);
  }
  
  /**
   * @return Object<{$this->getFQN()}>
   */
  public function newInstance(Array $params = array(), $dontCallConstructor = FALSE) {
    
    if ($dontCallConstructor) {
      // Creates a new instance of the mapped class, without invoking the constructor.
      if (!isset($this->prototype)) {
        $this->prototype = unserialize(sprintf('O:%d:"%s":0:{}', mb_strlen($this->getFQN()), $this->getFQN()));
      }
      
      return clone $this->prototype;
    }

    if (count($params) === 0) {
      return $this->getReflection()->newInstance();
    } else {
      return $this->getReflection()->newInstanceArgs($params);
    }
  }
  
  /**
   * Gibt die Klasse als PHP Code zurück
   *
   * die Klasse ist noch nicht mit Namespace vor class notiert!
   * Eingerückt wird mit 2 whitespaces.
   * Em Ende befindet sich hinter der schließenden Klammer ein LF
   */
  public function php() {
    $cr = "\n";
    
    $php = $this->phpDocBlock(0);
    
    $ms = array(self::MODIFIER_ABSTRACT => 'abstract',
                self::MODIFIER_FINAL => 'final'
               );
    
    foreach ($ms as $const => $modifier) {
      if (($const & $this->modifiers) == $const)
        $php .= $modifier.' ';
    }
    
    $php .= 'class '.$this->getClassName().' ';
    
    /* Extends */
    if (isset($this->parentClass)) {
      if ($this->parentClass->getNamespace() === $this->getNamespace()) {
        $php .= 'extends '.$this->parentClass->getClassName().' '; // don't prefix namespace
      } else {
        $php .= 'extends '.'\\'.$this->parentClass->getFQN().' ';
      }
    }
    
    /* Interfaces */
    if (count($this->getInterfaces()) > 0) {
      $php .= 'implements ';
      $that = $this;
      $php .= A::implode($this->getInterfaces(), ', ', function (GClass $iClass) use ($that) {
        if ($that->getNamespace() === $iClass->getNamespace()) {
          return $iClass->getClassName();
        } else {
          return '\\'.$iClass->getFQN();
        }
      });
      $php .= ' ';
    }
    
    $php .= '{'.$cr;
    /* Die Funktionen machen immer den Abstand nach oben. Also vor jedem Property und jeder Methode macht dies ein "\n" */

    /* Constants */
    $php .= A::joinc($this->getConstants(), '  '.$cr.'%s;'.$cr, function ($constant) {
      return $constant->php(2);
    });
    
    /* Properties */
    $php .= A::joinc($this->getProperties(), '  '.$cr.'%s;'.$cr, function ($property) {
      return $property->php(2); // hier jetzt auch wegen DocBlock einrücken
    });

    /* Methods */
    $php .= A::joinc($this->getMethods(), '  '.$cr.'%s'.$cr, function ($method) {
      return $method->php(2); // rückt selbst zwischen den Klammern ein, aber wir wollen ja den body z.b. auf 4 haben nicht auf 2
    });
    
    $php .= '}'; // kein umbruch hinten, immer abstand nach oben!
    return $php;
  }
  
  public function elevate(Reflector $reflector) {
    $this->reflector = $reflector;

    /* Values erst elevaten damit der namespace + name korrekt ist für equals bei declaring class */
    $this->defaultProperties = $this->reflector->getDefaultProperties();
    $this->elevateValues(
      'modifiers',
      'startLine',
      'endLine',
      array('srcFileName','getFileName'),
      array('name','getShortName'),
      'interfaces'
    );
    
    $this->setNamespace($this->reflector->getNamespaceName());
    
    /* ClassDocBlock */
    $this->elevateDocBlock($this->reflector);

    foreach ($this->reflector->getMethods() as $rMethod) {
      try {
        $gMethod = GMethod::reflectorFactory($rMethod);
      } catch (ReflectionException $e) { // das ist nicht die von php
        $e->appendMessage("\n".'Methode in: '.$this->getFQN());
        throw $e;
      }
      
      if ($gMethod->getDeclaringClass() instanceof GClassReference &&
          $gMethod->getDeclaringClass()->equals($this)) {
        $gMethod->setDeclaringClass($this); // replace Reference
        
        // source weitergeben
        $gMethod->setSrcFileName($this->srcFileName); 
      }
      
      $this->methods[$gMethod->getName()] = $gMethod;
      $this->methodsOrder[] = $gMethod->getName();
    }
    
    foreach ($this->reflector->getProperties() as $rProperty) {
      $gProperty = GProperty::reflectorFactory($rProperty, $this);

      if ($gProperty->getDeclaringClass() instanceof GClassReference &&
          $gProperty->getDeclaringClass()->equals($this)) {
        $gProperty->setDeclaringClass($this); // replace Reference
      }

      // nicht addProperty nehmen das würde declaringClass falsch setzen
      $this->properties[$gProperty->getName()] = $gProperty;
    }
    
    /* Interfaces */
    foreach ($this->interfaces as $name=>$interface) {
      $this->interfaces[$name] = new GClass($interface);
    }
    
    /* Constants */
    foreach ($this->reflector->getConstants() as $name => $value) {
      $constant = new GClassConstant($name, $this, $value);
      $this->constants[$constant->getName()] = $constant;
    }
    
    /* Static Properties */
    //@TODO
    
    if ($this->reflector->getParentClass() != NULL) {
      $this->parentClass = new GClass($this->reflector->getParentClass());
    }
    
    $this->elevated = TRUE;
    
    return $this;
  }
  
  public function elevateParent() {
    if (isset($this->parentClass)) {
      $this->parentClass->elevateClass();
      
      foreach ($this->parentClass->getAllProperties() as $gProperty) {
        // nicht addProperty nehmen das würde declaringClass falsch setzen
        $this->properties[$gProperty->getName()] = $gProperty;
      }

      foreach ($this->parentClass->getAllMethods() as $gMethod) {
        // nicht addMethod nehmen das würde declaringClass falsch setzen
        $this->methods[$gMethod->getName()] = $gMethod;
      }
    }
  }
  
  public function elevateClass() {
    if (!$this->elevated) {
      $this->elevate(new ReflectionClass($this->getFQN()));
    }
    return $this;
  }
  
  /**
   * Gibt nur die Properties der Klasse zurück
   *
   * @return GProperty[]
   */
  public function getProperties() {
    $properties = array();
    foreach ($this->properties as $property) {
      if ($property->getDeclaringClass() instanceof GClass && 
          $property->getDeclaringClass()->equals($this)) {
        $properties[$property->getName()] = $property;
      }
    }
    return $properties;
  }
  
  /**
   * Gibt Alle Properties der KlassenHierarchie zurück
   *
   * @return GProperty[]
   */
  public function getAllProperties() {
    return $this->properties;
  }
  
  public function getProperty($name) {
    return $this->properties[$name];
  }
  
  public function addProperty(GProperty $property) {
    $this->properties[$property->getName()] = $property;
    $property->setDeclaringClass($this);
    return $this;
  }

  /**
   * Gibt zurück ob die Klassenhierarchie das Property hat
   * 
   * @return bool
   */
  public function hasProperty($name) {
    return array_key_exists($name,$this->properties);
  }
  
  /**
   * Gibt zurück ob die Klasse das Property hat
   * @return bool
   */
  public function hasOwnProperty($name) {
    if (!$this->hasProperty($name)) return FALSE;
    
    $property = $this->getProperty($name);
    return $property->getDeclaringClass() instanceof GClass && $property->getDeclaringClass()->equals($this);
  }

  /**
   * Erstellt ein neues Property und fügt dieses der Klasse hinzu
   * 
   * @return GProperty
   */
  public function createProperty($name, $modifiers = GProperty::MODIFIER_PROTECTED, $default = 'undefined') {
    $gProperty = new GProperty($this);
    $gProperty->setName($name);
    $gProperty->setModifiers($modifiers);
    if (func_num_args() == 3) {
      $this->setDefaultValue($gProperty,$default);
    }
    $this->addProperty($gProperty);
    return $gProperty;
  }
  
  /**
   * Erstellt eine neue Methode und fügt diese der Klasse hinzu
   * 
   * @return GMethod
   */
  public function createMethod($name, $params = array(), $body = NULL, $modifiers = GMethod::MODIFIER_PUBLIC) {
    $method = new GMethod($name, $params, $body, $modifiers);
    $this->addMethod($method);
    return $method;
  }
  
  /**
   * @param const|int $position 0-indexed position oder self::PREPEND für anfang oder self::APPEND für ende
   */
  public function addMethod(GMethod $method, $position = self::APPEND) {
    $this->methods[$method->getName()] = $method;
    $this->setMethodOrder($method, $position);
    $method->setDeclaringClass($this);
    return $this;
  }
  
  public function setMethod(GMethod $method) {
    return $this->addMethod($method);
  }
  
  public function setMethodOrder(GMethod $method, $position) {
    if (!$this->hasMethod($method->getName())) {
      throw new \InvalidArgumentException('Methode muss erst der Klasse hinzugefügt werden. Erst dann darf setMethodOrder() aufgerufen werden');
    }
    \Webforge\Common\ArrayUtil::insert($this->methodsOrder, $method->getName(), $position);
    return $this;
  }

  /**
   * @return GMethod
   */
  public function getMethod($name) {
    return $this->methods[$name];
  }
  
  /**
   * @return GMethod
   */
  public function getParentsMethod($name) {
    if ($this->parentClass) {
      if ($this->parentClass->hasOwnMethod($name)) {
        return $this->parentClass->getMethod($name);
      } else {
        return $this->parentClass->getParentsMethod($name); // es kann nie zur exception komen wenn man vorher mit hasMethod gecheckt hat, dass es die methode in der hierarchy gibt
      }
    } else {
      throw new \RuntimeException('getParentsMethod für '.$this->getFQN().' ist nicht defined, da es keine parentClass gibt');
    }
  }
  
  /**
   * @return bool
   */
  public function hasMethod($name) {
    return array_key_exists($name,$this->methods);
  }

  /**
   * Gibt zurück ob die Klasse eine implementierung für diese Methode hat
   *
   * hasMethod gibt auch TRUE zurück wenn die Klassenhierarchy ein Interface hat, was diese Methode hat
   * diese Funktion gibt dann FALSE zurück
   *
   * @TODO isInterface
   * @return bool
   */
  public function hasConcreteMethod($name) {
    if (!array_key_exists($name,$this->methods)) {
      return FALSE;
    }
    $method = $this->getMethod($name);
    
    return !$method->getDeclaringClass()->isInterface();
  }

  /**
   * @return bool
   */
  public function hasOwnMethod($name) {
    return array_key_exists($name,$this->methods) && $this->methods[$name]->getDeclaringClass()->equals($this);
  }
  
  /**
   * Gibt die Methoden der Klasse zurück, die die Klasse definiert
   * 
   * @return array
   */
  public function getMethods() {
    $methods = array();
    foreach ($this->methodsOrder as $methodName) {
      $method = $this->methods[$methodName];
      if ($method->getDeclaringClass()->equals($this)) {
        $methods[$method->getName()] = $method;
      }
    }
    return $methods;
  }
  
  /**
   * Gibt die Methoden der Klasse und die Methoden der Parents der Klasse zurück
   *
   * @return array
   */
  public function getAllMethods() {
    $methods = array();
    foreach ($this->methodsOrder as $methodName) {
      $method = $this->methods[$methodName];
      $methods[$method->getName()] = $method;
    }
    return $methods;
  }
  
  /**
   * Erstellt Stubs (Prototypen) für alle abstrakten Methoden der Klasse
   */             
  public function createAbstractMethodStubs() {
    if ($this->isAbstract()) return $this;
    
    if (($parent = $this->getParentClass()) !== NULL) {
      $parent->elevateClass();
      foreach ($parent->getAllMethods() as $method) {
        if ($method->isAbstract()) {
          $this->createMethodStub($method);
        }
      }
    }
    
    foreach ($this->getAllInterfaces() as $interface) {
      $interface->elevateClass();
      foreach ($interface->getAllMethods() as $method) {
        $this->createMethodStub($method);
      }
    }
    return $this;
  }

  /**
   * Erstellt einen Stub für eine gegebene abstrakte Methode
   */
  public function createMethodStub(GMethod $method) {
    // method is not abstract (thats strange)
    if (!$method->isAbstract()) return $this;
    
    // no need to implement
    if ($this->hasMethod($method->getName()) && !$method->isAbstract()) return $this;
    
    $cMethod = clone $method;
    $cMethod->setAbstract(FALSE);
    $cMethod->setDeclaringClass($this);
    return $this->addMethod($cMethod);
  }


  /**
   * Gibt alle Konstanten der Klasse (selbst) zurück
   *
   * @return array
   */
  public function getConstants() {
    if (!isset($this->parentClass)) return $this->constants;
    
    $constants = array();
    foreach ($this->constants as $constant) {
      if (!$this->parentClass->hasConstant($constant->getName())) {
        $constants[$constant->getName()] = $constant;
      }
    }
    
    return $constants;
  }
  
  /**
   * @return Psc\Code\Generate\GClassConstant
   */
  public function getConstant($name) {
    return $this->constants[mb_strtoupper($name)];
  }

  /**
   * @return bool
   */
  public function hasConstant($name) {
    return array_key_exists(mb_strtoupper($name), $this->constants);
  }

  /**
   * @return bool
   */
  public function hasOwnConstant($name) {
    return array_key_exists(mb_strtoupper($name), $this->getConstants());
  }

  /**
   * Gibt alle Konstanten der Klassenhierarchie zurück
   * @return GClassConstant[]
   */
  public function getAllConstants() {
    return $this->constants;
  }
  
  public function hasDefaultValue(GProperty $property) {
    return array_key_exists($property->getName(), $this->defaultProperties);
  }

  public function getDefaultValue(GProperty $property) {
    if (array_key_exists($n = $property->getName(), $this->defaultProperties)) {
      return $this->defaultProperties[$n];
    }
  }
  
  public function setDefaultValue(GProperty $property, $default) {
    $this->defaultProperties[$property->getName()] = $default;
    return $this;
  }
  
  public function removeDefaultValue(GProperty $property) {
    if (array_key_exists($n = $property->getName(), $this->defaultProperties)) {
      unset($this->defaultProperties[$n]);
    }
  }
  
  /**
   * @return Name der Klasse mit Namespace immer mit \ davor!
   */
  public function getName() {
    //return ($this->namespace ? $this->namespace.'\\' : NULL).$this->name;
    return $this->namespace.'\\'.$this->name;
  }
  
  /**
   * Gibt den FQN (Namespace + Name) der Klasse ohne \ davor zurück
   *
   * dies braucht man z. B. für die meisten Doctrine Klassen etc, da der Name in Strings keinen \ davor brauch um
   * full qualified zu sein ( i don't get it)
   * @return string
   */
  public function getFQN() {
    return ltrim($this->getName(),'\\');
  }
  
  /**
   * Setzt den Namen der Klasse
   *
   * Namespace wird immer mitgesetzt! D. h. übergibt man hier nur den ShortName wird der Namespace auf NULL gesetzt
   * Wenn man nur den Namen der Klasse und nicht den Namespace wechseln will, muss man setClassName() nehmen
   * @param string FQN;
   */
  public function setName($name) {
    if (mb_strpos($name,'\\') !== FALSE) {
      $this->setNamespace(Code::getNamespace($name));
      $this->name = Code::getClassName($name);
    } else {
      $this->name = $name;
      $this->namespace = NULL;
    }
    return $this;
  }
  
  /**
   * @param string $className nicht FQN Name (Name ohne Namespace)
   */
  public function setClassName($className) {
    $this->name = $className;
    return $this;
  }
  
  public function setNamespace($ns) {
    if (mb_strpos($ns,'\\') !== 0) $ns = '\\'.$ns;
    $this->namespace = $ns;
    return $this;
  }
  
  public function isAbstract() {
    return ($this->modifiers & self::MODIFIER_ABSTRACT) == self::MODIFIER_ABSTRACT;
  }

  public function isFinal() {
    return ($this->modifiers & self::MODIFIER_FINAL) == self::MODIFIER_FINAL;
  }
  
  public function setFinal($bool) {
    return $this->setModifier(self::MODIFIER_FINAL,$bool);
  }

  public function setAbstract($bool) {
    return $this->setModifier(self::MODIFIER_ABSTRACT,$bool);
  }
    
  /**
   * Ohne Namespace
   * @return string
   */
  public function getClassName() {
    return $this->name;
  }
  
  /**
   * mit \ davor es seie denn es gibt keinen dann NULL
   */
  public function getNamespace() {
    return $this->namespace;
  }
  
  /**
   * Im Gegensatz zum Konstruktor überprüft diese Funktion strings nach der Existenz der Klasse
   *
   * @param string|ReflectionClass $class
   * @return Psc\Code\Generate\GClass
   */
  public static function factory() {
    $class = func_get_arg(0);
    
    if (is_string($class) && (class_exists($class) || interface_exists($class))) {
      $class = new ReflectionClass($class);
    }
    
    return new static($class);
  }
  
  /**
   * @return \ReflectionClass
   */
  public function getReflection() {
    if (!isset($this->reflector)) {
      $this->reflector = new ReflectionClass($this->getFQN());
    }
    return $this->reflector;
  }
  
  /**
   * @param GClass $parentClass
   * @chainable
   */
  public function setParentClass(GClass $parentClass) {
    $this->parentClass = $parentClass;
    return $this;
  }

  /**
   * @return GClass
   */
  public function getParentClass() {
    return $this->parentClass;
  }

  // Interfaces
  /**
   * Gibt nur die Interfaces der Klasse zurück
   *
   * @TODO fixme: interfaces die subinterfaces von interfaces sind die wir schon hinzugefügt haben, dürfen auch nicht rein
   * @return GClass[]
   */
  public function getInterfaces() {
    /* das hier ist etwas mehr tricky, als bei property und method weil interfaces auch in reflection
       eine reflectionClass sind und deshalb keine getDeclaringClass() Methode haben
       deshalb gucken wir ob parent das Interface implementiert. Wenn ja tun wir so als wäre dies nicht unser eigenes implements
       
       dies hilft beim Klasse-Schreiben jedoch nicht. Dort würde das Interface dann immer wieder verschwinden,
       auch wenn es explizit hinzugefügt worden wäre
       
       das ist nicht so trivial:
       InterfaceC extends InterfaceA, InterfaceB
       dann dürfen InterfacA und InterfaceB nicht implementiert werden (nur C)
    */
    if (!isset($this->parentClass)) return $this->interfaces;
    
    $interfaces = array();
    foreach ($this->interfaces as $interface) {
      if (!$this->parentClass->hasInterface($interface)) {
        $interfaces[$interface->getFQN()] = $interface;
      }
    }
    return $interfaces;
  }

  /**
   * Gibt alle Interfaces der Klassenhierarchie zurück
   *
   * @return GClass[]
   */
  public function getAllInterfaces() {
    return $this->interfaces;
  }
  
  
  /**
   * Fügt der Klasse ein Interface hinzu (implements)
   *
   */
  public function addInterface(GClass $class) {
    $this->interfaces[$class->getFQN()] = $class;
    return $this;
  }
  
  /**
   * Entfernt das Interface aus der Klasse
   * 
   * @chainable
   */
  public function removeInterface(GClass $class) {
    if (array_key_exists($n = $class->getFQN(), $this->interfaces)) {
      unset($this->interfaces[$n]);
    }
    return $this;
  }
  
  /**
   * @return bool
   */
  public function hasInterface(GClass $class) {
    if (!$this->isElevated()) {
      return $this->getReflection()->implementsInterface($class->getFQN());
    }
    
    return array_key_exists($class->getFQN(), $this->interfaces);
  }
  
  /**
   * Fügt eine Klasse der GClass hinzu, die ins "use" geschrieben werden soll, wenn die GClass mit einem GClassWriter geschrieben wird
   *
   * @deprecated lieber das dem GClassWriter direkt hinzufügen
   */
  public function addUsedClass(GClass $class, $requestedAlias = NULL) {
    if (array_key_exists($fqn = $class->getFQN(), $this->usedClasses)) {
      if ($this->usedClasses[$fqn][1] != $requestedAlias) {
        throw new Exception(sprintf("Die Klasse '%s' kann nicht mit alias '%s' hinzugefügt werden, da sie schon als Alias '%s' hinzugefügt wurde",
                                    $fqn, $requestedAlias, $this->usedClasses[$fqn][1]
                                    ));
      }
    } else {
      $this->usedClasses[$fqn] = array($class, $requestedAlias);
    }
    return $this;
  }
  
  public function removeUsedClass(GClass $class) {
    if (array_key_exists($fqn = $class->getFQN(), $this->usedClasses)) {
      unset($this->usedClasses[$fqn]);
    }
    return $this;
  }
  
  /**
   * @return bool
   */
  public function equals(GClass $otherClass) {
    return $this->getFQN() === $otherClass->getFQN();
  }
  
  /**
   * @return bool
   */
  public function exists($autoload = TRUE) {
    try {
      return class_exists($this->getFQN(), $autoload);
    } catch (\Psc\ClassLoadException $e) {
      return FALSE;
    }
  }
  
  /**
   * @return array
   */
  public function getUsedClasses() {
    return $this->usedClasses;
  }

  public function __toString() {
    return '[class '.__CLASS__.': '.$this->getName().']';
  }
}