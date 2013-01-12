<?php

namespace Psc\CMS\Controller;

interface SortingController {
  
  /**
   * Gibt das Sortable Field des Entities, welches der Controller verwaltet zurück
   *
   * dieses field wird zum auslesen der Reihenfolge der Entities benutzt (ORDER BY $field )
   * @return string
   */
  public function getSortField();
  
  /**
   * Speichert die Sortierung der Entities
   *
   * die sortMap ist ein flacher Array mit $sort => $identifier
   * $sort ist hierbei numerisch und fängt bei 0 an
   */
  public function saveSort(Array $sortMap);
}
?>