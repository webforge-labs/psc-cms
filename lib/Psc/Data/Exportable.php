<?php

namespace Psc\Data;

/**
 * Ein Generelles Interface für Klasse die Exportierbar sind
 *
 * Exportierbare Klassen sind auch dadurch serialisierbar, dass man sie zuerst Exportiert
 * 
 * z. B. Exporte wie der JSON-Export sind hiermit möglich
 */
interface Exportable {
  
  /**
   * Exportiert den Zustand des Objektes (alle Properties) in ein stdClass-Objekt
   *
   * Mit diesen Daten ist es möglich das Objekt zu serialisieren und wieder zu Importieren
   * @return stdClass|Array
   */
  public function export();
  
}
?>