<?php

namespace Psc\Data\Type;

/**
 * Das Interface Traversable Type verhält sich wie das Traversable SPL Interface:
 * also Array + Collection sind valide.
 *
 * zusäztlich erweitern wir dieses auch mit DefaultValueType.
 * Dies machen wir aus der "not" heraus nicht ein weiteres Interface Wort für "Array oder Collection" zu erfinden zu müssen
 */
interface TraversableType extends EnclosingType, DefaultValueType {
  
  /**
   * Gibt zurück ob die Schlüssel des Traversables relevant sind, oder das Traversable eine einfache sortierte Aufzählung ist
   *
   * z. B. bei exportfunktionen steuert dies, ob die Schlüssel explizit mit ausgegeben werden sollen
   * @return bool 
   */
  public function isList();
  
}
?>