<?php

namespace Psc\Data\Type;

/**
 * Ein Typ der ein Interface definiert, welches den Typ implementiert
 *
 * z. B. ist
 * LinkType ist ein InterfacedType mit dem Interface: \Psc\Type\Interfaces\Link und wird durch \Psc\UI\Link implementiert
 *
 * ist ein Type ein InterfacedType wird als StandardImplementierung von getPHPType der Klassenname des Interfaces zurückgegeben
 * (ebenso getPHPHint)
 */
interface InterfacedType {
  
  /**
   * Gibt das Interface zurück welches den Type implementiert
   * 
   * @return string der FQN des Interfaces der Klasse ohne \ davor.
   */
  public function getInterface();
  
}