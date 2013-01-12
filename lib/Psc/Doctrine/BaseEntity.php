<?php

namespace Psc\Doctrine;

/**
 * Der erste Layer der Entity Klassen
 *
 * hier sollte absolute Base-Funktionalität eingebaut werden, die gegebenenfalls auch schnell selbst geschrieben werden kann
 * 
 */
abstract class BaseEntity {
  
  /**
   * Ruft den Setter für das Feld $field auf dem Entity auf
   *
   * @param string $field der Name des Feldes in CamelCase
   * @return die Rückgabe des Setters
   */
  public function callSetter($field, $value) {
    $f = 'set'.ucfirst($field);
    if (!method_exists($this, $f)) throw new \InvalidArgumentException($f.'() existiert nicht in '.\Psc\Code\Code::getClass($this));
    return $this->$f($value);
  }
  
  /**
   * Ruft den Getter für das Feld $field auf dem Entity auf
   *
   * @param string $field der Name des Feldes in CamelCase
   * @return mixed
   */
  public function callGetter($field) {
    $f = 'get'.ucfirst($field);
    if (!method_exists($this, $f)) throw new \InvalidArgumentException($f.'() existiert nicht in '.\Psc\Code\Code::getClass($this));
    return $this->$f();
  }
  
  /**
   * @return string
   */
  public function __toString() {
    try {
      return $this->getEntityLabel();
    /* wir duerfen hier eh keine Exception schmeissen und die Anwendung hält, so oder so
       so sehen wir wenigstens die Exception und verursachen das gleiche Verhalten, wie wenn wir sie nicht catchen
    */
    } catch (\Exception $e) {
      print $e;
      \Psc\PSC::terminate();
    }
    
    return '';
  }
}
?>