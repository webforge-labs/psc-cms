<?php

namespace Psc\Doctrine;

/**
 * DAS Entity Interface
 *
 * Dieses Interface ist das technische Interface eines Entities welches alles beeinhaltet was man braucht,
 * um ein Entity mit Doctrine in der Datenbank abzuspeichern.
 * Alle LowLevel Doctrine-Datenbank Funktionen können mit diesem Interface arbeiten, da Informationen
 * wie z. B. TabsLabels oder ContentData nicht nötig sind
 */
interface Entity {

 /**
   * Gibt den FQN des Entities zurück
   *
   * weil get_class() hier auch sowas wie entitiesProxy sein kann, machen wir das hier save
   * und strings zurückgeben ist auch schneller ..
   * und wir müssen es EINMAL im Projekt machen und wir können ein Template dafür bauen ..
   * @return string FQN voll qualifizierter Name des Entities (mit Namespaces mit \ getrennt, aber nicht am Anfang)
   */
  public function getEntityName();
  
  /**
   * Gibt den Primärschlüssel des Entities zurück
   *
   * @return mixed meistens jedoch einen int > 0 der eine fortlaufende id ist
   */
  public function getIdentifier();

  
  /**
   * @param mixed $identifier
   * @chainable
   */
  public function setIdentifier($identifier);

  /**
   * Überprüft ob das Entity mit einem Objekt identisch ist
   *
   * zwei Entities sind gleich, wenn sie den gleichen EntityName haben und denselben Identifier
   * @param Psc\Doctrine\Entity<$this->getEntityName()> $otherEntity 
   * @return bool
   */
  public function equals(Entity $otherEntity = NULL);

  /**
   * Gibt ein Label zurück, was das Entity Technisch möglichst gut beschreibt
   *
   * Dieses Label wird vor allem in Debug-Funktionen genutzt.
   * Das Label sollte mindstens den EntityNamen und den EntityIdentifier beeinhalten
   * @return string
   */
  public function getEntityLabel();
  
  
  /**
   * Gibt TRUE zurück wenn das Entity noch nie in der Datenbank gespeichert wurde
   *
   * (identifier ist dann nicht definiert)
   * @return bool
   */
  public function isNew();


  public function callSetter($field, $value);
  

  public function callGetter($field);

}
?>