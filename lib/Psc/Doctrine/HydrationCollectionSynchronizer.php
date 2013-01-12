<?php

namespace Psc\Doctrine;

use Psc\Data\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Der HydrationCollectionSynchronizer ist eine abstracte Basis Klasse für alle Synchronizer die die fremde collection während der Synchronisation hydrieren
 *
 * er ist Basisklasse für den ActionsSynchronizer (PersistentCollectionSynchronizer)
 */
abstract class HydrationCollectionSynchronizer extends EventCollectionSynchronizer {

  /**
   * Synchronisiert eine Collection von (Datenbank-)Objekten mit einer Collection von Daten / Unique-Kriterien oder Exportieren Objekten
   *
   * Die $fromCollection ist die Collection der bereits hydrierten Objekte. (fertige Tags)
   * die $toCollection ist die noch nicht hydrierte Collection der Objekte die Objekte in $fromCollection repräsentieren (Tag Labels)
   * 
   * zur Hilfe werden zwei Funktionen benötigt.
   * Die hydrierFunktion erstellt aus den Daten asu $toCollection ein Objekt aus $fromCollection ( $label ===> Entity $tag )
   * die hash Funktion erstellt aus einem Objekt von $fromCollection einen Skalar ( Entity $tag => $id )
   *
   * Article und Test beispiel:
   *
   * $fromCollection = $article->getTags();
   * $toCollection = array('Tag1Name','Tag2Name','....');
   *
   * so ungefähr!! muss NULL zurückgeben nicht array
   * $hydrateUniqueObject = function (\stdClass $json) {
   *    return $em->createQueryBuilder()
   *      ->select('tag')->from('Entities\Tag')
   *      ->where('id = :id OR uniqueconstraint = :unique)
   *      ->setParameter(
   *         array(
   *           'id'=>$json->id,
   *           'unique'>$json->label
   *         )
   *       )
   *    ;
   * };
   *
   * $hashObject = function (Tag $tag) {
   *   return $tag->getIdentifier();
   * };
   *
   * @param ArrayCollection $dbCollection die Collection als fertige Objekte aus der Datenbank
   * @param collection $toCollection eine noch nicht hydrierte Collection von Objekten die Objekte in $fromCollection repräsentieren kann
   *
   * @return list($insert,$updates,$deletes) die jeweiligen listen von dispatchten events
   */
  public function process($fromCollection, $toCollection) {
    // wir haben keine Ahnung welche Objekte in der $fromCollection und welche in der $toCollection sind
    
    $fromCollection = \Psc\Code\Code::castArray($fromCollection); // damit wir eine copy machen
    $updates = $inserts = $deletes = array();
    $index = array();
    foreach ($toCollection as $toCollectionKey => $toObject) {
      
      $fromObject = $this->hydrateUniqueObject($toObject, $toCollectionKey);
      
      // kein objekt konnte hydriert werden
      if ($fromObject === NULL) {
        $inserts[] = $this->dispatchInsert($toObject);
      
        // inserts müssen nicht in den index, da sie nicht im universum gefunden wurden, können sie auch nicht in $fromCollection sein
      } else {
        // objekt hydriert, kann mit korrekter id sein oder matching unique-constraint
        $updates[] = $this->dispatchUpdate($fromObject, array('from'=>$fromObject,'to'=>$toObject));
        
        $index[$this->hashObject($fromObject)] = TRUE;
      }
    }
    
    foreach ($fromCollection as $fromObject) {
      if (!array_key_exists($this->hashObject($fromObject), $index)) { // object ist weder ein insert noch ein update
        $deletes[] = $this->dispatchDelete($fromObject);
      }
    }

    return array($inserts, $updates, $deletes);
  }
  
  /**
   * Hydriert aus einer Darstellung aus $toCollection ein Objekt von $fromCollection (oder von dessen Universum)
   *
   * kann kein Objekt hydriert werden muss NULL zurückgegeben werden
   */
  abstract public function hydrateUniqueObject($toObject, $toCollectionKey);
  
  
  /**
   * Erstellt aus einem Objekt aus $fromCollection (oder aus dessen Universum) einen Skalaren Hash
   *
   * @return scalar
   */
  abstract public function hashObject($fromObject);
}
?>