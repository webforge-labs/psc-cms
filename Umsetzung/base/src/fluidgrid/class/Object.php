<?php

/**
 * 
 * Base Klasse für alle Objekte aus dem FluidGrid Modul. Vll ein paar Basic Funktionen hier
 *
 * 
 */
class FluidGrid_Object {
  /**
   * Simuliert Setter / Gett für alle Variablen des Objektes
   * 
   */
  public function __call($method, $args = NULL) {
    $prop = strtolower(substr($method,3,1)).substr($method,4);

    /* getter der nicht implementiert ist */
    if (strpos($method, 'get') === 0) {
      
      if (property_exists($this,$prop)) {
        return $this->$prop;
      }
    }

    /* setter der nicht implementiert ist */
    if (strpos($method, 'set') === 0) {
      
      if (property_exists($this,$prop)) {
        $this->$prop = $args[0];
        return $this;
      }
    }

  
    throw new BadMethodCallException('Call to undefined method '.get_class($this).'::'.$method.'()');
  }

  public function __get($field) {
    /* wir wollen auf jeden fall den Getter des Properties aufrufen */
    return call_user_func(array($this,'get'.ucfirst($field)));
  }


  public function __set($field, $value) {
    /* wir wollen auf jeden fall den Getter des Properties aufrufen */
    return call_user_func(array($this,'set'.ucfirst($field)));
  }
}
?>