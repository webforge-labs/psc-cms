<?php

/**
 * 
 * Base Klasse für alle Objekte aus dem Modul. Vll ein paar Basic Funktionen hier
 *
 * 
 */
class Object {
  /**
   * Simuliert Setter / Gett für alle Variablen des Objektes
   * 
   */
  public function __call($method, $args = NULL) {
    $prop = mb_strtolower(mb_substr($method,3,1)).mb_substr($method,4);

    /* getter der nicht implementiert ist */
    if (mb_strpos($method, 'get') === 0) {
      
      if (property_exists($this,$prop)) {
        return $this->$prop;
      }
    }

    /* setter der nicht implementiert ist */
    if (mb_strpos($method, 'set') === 0) {

      if (property_exists($this,$prop)) {
        $this->$prop = $args[0];
        return $this;
      }
    }

  
    throw new BadMethodCallException('Call to undefined method '.get_class($this).'::'.$method.'()');
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

  public function getClass() {
    return get_class($this);
  }

  public function hasMethod($method) {
    return method_exists($this,$method);
  }

  public function __toString() {
    return '[class '.$this->getClass().' not converted to string]';
  }

  /**
   * Erstellt ein neues Objekt und gibt dieses zurück
   * 
   * Die Argument für den Konstruktor können ganz normal übergeben werden
   * @param mixed $arg,...
   * @return Object
   */
  public static function factory($className) {
    $args = func_get_args();
    
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
        $rfc = new ReflectionClass($className);
        
        // use Reflection to create a new instance, using the $args
        return $rfc->newInstanceArgs($args); 
    }
  }


  /**
   * Erstellt ein neues Objekt und gibt dieses zurück
   * 
   * Die Argument für den Konstruktor können ganz normal übergeben werden
   * @param mixed $arg,...
   * @return Object
   */
  public function callMethod($name, Array $args = NULL) {
    $args = (array) $args;

    /* funky switch da wir performance wollen */
    switch (count($args)) {
      case 0:
        return $this->$name;

      case 1:
        return $this->$name($args[0]);
        
      case 2:
        return $this->$name($args[0],$args[1]);

      case 3:
        return $this->$name($args[0],$args[1],$args[2]);

      case 4:
        return $this->$name($args[0],$args[1],$args[2],$args[3]);

      case 5:
        return $this->$name($args[0],$args[1],$args[2],$args[3],$args[4]);

      case 6:
        return $this->$name($args[0],$args[1],$args[2],$args[3],$args[4],$args[5]);

      case 7:
        return $this->$name($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6]);

      case 8:
        return $this->$name($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6],$args[7]);

      default: 
        // use call_user_func_array evil
        return call_user_func_array(array($this,$name),$args);
    }
  }
}
?>