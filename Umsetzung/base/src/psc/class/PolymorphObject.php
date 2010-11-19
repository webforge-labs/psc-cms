<?php
/**
 * @TODO die Definition von polymorphConstructors kompilierbar machen
 * 
 */
class PolymorphObject extends Object {
  
  /**
   * 
   * $this->polymorphConstructors muss ein Array sein
   * 
   * Dieses Array kann verschiedene Construktor-Signaturen enthalten:
   * 
   * $this->polymorphConstructors = array(
   *  'default1' => array('int','string','object:Dir'),
   *  'default2' => array('string','int','int'),
   * );
   * 
   * die Typen müssen dabei mit den Werten von Code::getType() übereinstimmen
   * der Konstruktor wird dann mit $this->construct$Name aufgerufen
   */ 
  public function __construct() {
    $args = func_get_args();

    /* wir bauen den vergleichsarray mit den arguments */
    $signatur = array();
    foreach ($args as $arg) {
      $signatur[] = Code::getType($arg);
    }
    
    
    foreach ($this->polymorphConstructors as $name => $params) {
      if ($signatur === $params) {
        return $this->callMethod('construct'.ucfirst($name),$args);
      }
    }

    throw new BadMethodCallException('Es wurde kein Konstruktor für die Parameter: '.implode(', ',$signatur).' gefunden. (Ist $this->polymorphConstructors korrekt?)');
  }
}

?>