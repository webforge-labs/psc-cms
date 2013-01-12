<?php

namespace Psc\CMS;

interface TabsContentItem {
  
  public function getTabsLabel();
  
  /**
   * $qvars überschreibt die qvars die das Item selbst setzt
   */
  public function getTabsURL(Array $qvars = array());
  
  /**
   * @return list($typeName, $identifier)
   */
  public function getTabsId();
  
  /**
   * Gibt zusätzliche Daten, die nicht im Objekt gespeichert werden können und die z. B. für das erstellen der URL benötigt werden zurück
   *
   * dies können z. B. Kontext-Daten sein, die aber im Objekt selbst keinen Sinn machen
   * @return array
   */
  public function getTabsData();
  
  /**
   * Erhält die zusätzlichen Daten als FormularDaten, wie es sie bei getTabsData wegschickt
   *
   * Das Objekt sollte die Daten hydrieren und so setzten, dass getTabsData diese wieder zurückgeben kann
   * @chainable
   */
  public function setTabsData(Array $data);

}

?>