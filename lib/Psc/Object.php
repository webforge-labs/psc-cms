<?php

namespace Psc;

/**
 * 
 * Base Klasse für alle Objekte aus dem Modul. Vll ein paar Basic Funktionen hier
 *
 * 
 */
class Object extends SimpleObject {
  
  const REPLACE_ARGS = 'PscObjectConstant_REPLACE_ARGS';
  
  /**
   * Simuliert Setter / Gett für alle Variablen des Objektes
   * 
   */
  public function __call($method, $args = NULL) {
    $prop = mb_strtolower(mb_substr($method,3,1)).mb_substr($method,4); // ucfirst in mb_string
    
    // wir normalisieren ein paar properties, weils nervt
    $prop = str_replace(
                        array('cSS','hTML','jS','oID','xML'
                        ),
                        array('css','html','js','oid','xml'
                        ),
                        $prop
                       );

    /* getter der nicht implementiert ist */
    if (mb_strpos($method, 'get') === 0) {
      
      if (property_exists($this,$prop)) {
        return $this->$prop;
      } else {
        throw new MissingPropertyException($prop, 'Undefined Property: '.get_class($this).'::$'.$prop);
      }
    }

    /* setter der nicht implementiert ist */
    if (mb_strpos($method, 'set') === 0) {

      if (property_exists($this,$prop)) {
        $this->$prop = $args[0];
        return $this;
      } else {
        throw new MissingPropertyException($prop, 'Undefined Property: '.get_class($this).'::$'.$prop);
      }
    }
  
    throw new \BadMethodCallException('Call to undefined method '.get_class($this).'::'.$method.'()');
  }

  public function __get($field) {
    /* wir wollen auf jeden fall den Getter des Properties aufrufen */
    $f = 'get'.ucfirst($field);
    
    return $this->$f();
  }


  public function __set($field, $value) {
    /* wir wollen auf jeden fall den Setter des Properties aufrufen */
    $f = 'set'.ucfirst($field);
    return $this->$f($value);
  }
  

  /**
   * Erstellt ein neues Objekt und gibt dieses zurück
   * 
   * Die Argument für den Konstruktor können ganz normal übergeben werden
   * Der Dritte Parameter kan self::REPLACE_ARGS sein, dann wird der zweite Parameter als Parameters interpretiert
   * @param string $className der Name der Klasse
   * @param mixed $arg,...
   * @return Object
   */
  public static function factory() {
    $args = func_get_args();
    $className = array_shift($args); // className entfernen
        
    if (isset($args[1]) && $args[1] == self::REPLACE_ARGS) {
      $args = $args[0];
    }
    
    if (!class_exists($className)) {
      throw new \Psc\Exception($className. ' kann nicht geladen werden');
    }

    /* funky switch da wir performance wollen */
    switch (count($args)) {
      case 0:
        return new $className;

      case 1:
        return new $className($args[0]);
        
      case 2:
        return new $className($args[0],$args[1]);

      case 3:
        return new $className($args[0],$args[1],$args[2]);

      case 4:
        return new $className($args[0],$args[1],$args[2],$args[3]);

      case 5:
        return new $className($args[0],$args[1],$args[2],$args[3],$args[4]);

      case 6:
        return new $className($args[0],$args[1],$args[2],$args[3],$args[4],$args[5]);

      case 7:
        return new $className($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6]);

      case 8:
        return new $className($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6],$args[7]);

      default: 
        $rfc = new \ReflectionClass($className);
        
        // use Reflection to create a new instance, using the $args
        return $rfc->newInstanceArgs($args); 
    }
  }


  
  /**
   * Ruft eine Methode auf einem Objekt auf
   * 
   * @param object $object
   * @param string $name der Name der Methode die aufgerufen werden soll
   * @param array $arg
   * @return Object
   */
  public static function callObjectMethod($object, $name, Array $args = NULL) {
    $args = (array) $args;

    /* funky switch da wir performance wollen */
    switch (count($args)) {
      case 0:
        return $object->$name();

      case 1:
        return $object->$name($args[0]);
        
      case 2:
        return $object->$name($args[0],$args[1]);

      case 3:
        return $object->$name($args[0],$args[1],$args[2]);

      case 4:
        return $object->$name($args[0],$args[1],$args[2],$args[3]);

      case 5:
        return $object->$name($args[0],$args[1],$args[2],$args[3],$args[4]);

      case 6:
        return $object->$name($args[0],$args[1],$args[2],$args[3],$args[4],$args[5]);

      case 7:
        return $object->$name($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6]);

      case 8:
        return $object->$name($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6],$args[7]);

      default: 
        // use call_user_func_array evil
        return call_user_func_array(array($object,$name),$args);
    }
  }
}
?>