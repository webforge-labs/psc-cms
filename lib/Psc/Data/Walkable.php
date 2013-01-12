<?php

namespace Psc\Data;

interface Walkable {
  
  /**
   * Gibt alle Felder des Objektes zurück, die gewalked (exportiert oder was auch immer) werden sollen
   *
   * Die Schlüssel sollten die Feldernamen sein die Parameter für getWalkableMeta sind
   * Die Werte sind die Werte der Felder
   * @return Iterable
   */
  public function getWalkableFields();

  /**
   * Gibt den Typ des Feldes $field zurück
   * 
   * @return Psc\Data\Type\*
   */
  public function getWalkableType($field);
  
  /**
   * Gibt den Typ eines Elementes in einem Array, der in einem $field ist, zurück
   */
  //public function getWalkableArrayEntryType($entry, $key);
}
?>