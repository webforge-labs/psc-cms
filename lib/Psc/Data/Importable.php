<?php

namespace Psc\Data;


/**
 * Ein Generelles Interface für Klasse die Exportierbar sind
 *
 * Exportierbare Klassen sind auch dadurch serialisierbar, dass man sie zuerst Exportiert
 * 
 * z. B. Exporte wie der JSON-Export sind hiermit möglich
 */
interface Importable {
  
  /**
   * Importiert den Zustand eines exportierten Objektes aus ein stdClass-Objekt in eine neue instanz
   *
   * dies ist das Gegenstück zu Exportable. Für eine Klasse die Importable und Exportable implementiert gilt:
   *
   * // ClassOfObject $object
   * $test->assertEquals($object, ClassOfObject::import($object->export()));
   *
   * @return Objekt-Instanz der Klasse auf der import() afugerufen wurde
   */
  public static function import(\stdClass $o);
  
}
?>