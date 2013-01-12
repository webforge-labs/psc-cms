<?php

namespace Psc\PHPJS;

interface Object {
  
  
  /**
   * Gibt den Namen der Klasse zurück
   * 
   * @return string
   */
  public function getClass();
  
  /**
   * Gibt einen array von Feldnamen zurück, die Serialisiert werden sollen
   * @return array
   */
  public function getJSFields();

  /**
   * Erstellt ein neues Objekt mit den Daten aus einem Javascript Objekt
   * @param array $data die Schlüssel sind die Feldnamen, die Werte die Werte des Objektes
   * @param string $c der Namen der Klasse, die zurückgegeben werden MUSS
   */
  public static function factoryJSData(Array $data, $c);
  
}