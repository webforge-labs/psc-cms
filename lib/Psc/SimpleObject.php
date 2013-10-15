<?php

namespace Psc;

use Psc\Code\Code;
use Psc\Data\Type\Type;
use Psc\Data\Type\ObjectType;

/**
 * Die Mutter Object Klasse
 *
 * hat ein paar kleine Helfer für immer wiederkehrende Aufgaben
 */
class SimpleObject {

  /**
   * $this->invalidArgument(1, $param1, 'PositiveInteger', __FUNCTION__);
   * @param string|Type $expected der Type oder ein String der den Typ beschreibt
   * @cc-ignore
   */
  protected function invalidArgument($num, $actual, $expected, $function) {
    $class = Code::getClass($this);
    $namespace = Code::getNamespace($class);
    $context = $class.'::'.$function.'()';
    
    $expandType = function ($type) use ($class, $namespace) {
      if (is_string($type)) {
        $type = Type::create($type);
        if ($type instanceof ObjectType) {
          $type->expandNamespace($namespace);
        }
      }
      return $type;
    };
    
    if (is_array($expected)) {
      $composite = Type::create('Composite');
      foreach ($expected as $type) {
        $composite->addComponent($expandType($type));
      }
      $expected = $composite;
    } else {
      $expected = $expandType($expected);
    }
    
    return Exception::invalidArgument($num, $context, $actual, $expected);
  }

  /**
   * @deprecated
   * @cc-ignore
   */
  public function getClass() {
    if (PSC::getProject()->isDevelopment()) {
      throw new \Psc\DeprecatedException('getClass ist deprecated. Bitte \Psc\Code\Code::getClass($this) benutzen');
    }
    return get_class($this);
  }
  
  
  /**
   * Gibt den Namen der Klasse ohne Namespaces zurück
   *
   * discouraged: lieber code nehmen
   * @return string
   * @deprecated
   * @cc-ignore
   */
  public function getClassName() {
    return Code::getClassName(Code::getClass($this));
  }

  /**
   * @cc-ignore
   */
  public function hasMethod($method) {
    return method_exists($this,$method);
  }
  
  /**
   * @cc-ignore
   */
  public function hasStaticMethod($method) {
    return method_exists($this->getClass(),$method);
  }

  /**
   * @cc-ignore
   */
  public function __toString() {
    return '[class '.Code::getClass($this).' not converted to string]';
  }
  
  /**
   * Leider ist dies nicht so geil wie anfangs gedacht,
   * denn so richtig unique ist dies für einen request nicht, da kann einiges bei schief gehen
   * @cc-ignore
   */
  public static function getObjectId($object) {
    if (!is_object($object)) return NULL;
    return spl_object_hash($object);
  }

  /**
   * @cc-ignore
   */
  public function callSetter($field, $value) {
    $f = 'set'.ucfirst($field);
    return $this->$f($value);
  }
  
  /**
   * @cc-ignore
   */
  public function callGetter($field) {
    $f = 'get'.ucfirst($field);
    return $this->$f($field);
  }
}
?>