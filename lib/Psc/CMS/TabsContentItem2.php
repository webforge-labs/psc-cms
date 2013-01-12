<?php

namespace Psc\CMS;

/**
 * V2 des TabsContentItems
 *
 * (ich habe keine Ahnung, wie ich sonst den "Zwischenstand" überbrücken soll)
 */
interface TabsContentItem2 {

  const CONTEXT_DEFAULT = 'default';
  const CONTEXT_LINK = 'link';
  const CONTEXT_TAB = 'tab';
  const CONTEXT_BUTTON = 'button';
  const CONTEXT_AUTOCOMPLETE = 'autocomplete';
  const CONTEXT_FULL = 'full';
  
  const LABEL_DEFAULT = self::CONTEXT_DEFAULT;
  const LABEL_LINK = self::CONTEXT_LINK;
  const LABEL_TAB = self::CONTEXT_TAB;
  const LABEL_BUTTON = self::CONTEXT_BUTTON;
  const LABEL_AUTOCOMPLETE = self::CONTEXT_AUTOCOMPLETE;
  const LABEL_FULL = self::CONTEXT_FULL;
  
  
  /**
   * Das Label für die Anzeige im CMS
   *
   * @param const $content LABEL_* Konstante
   */
  public function getTabsLabel($context = 'default');
  
  /**
   * Gibt die Ajax-URL des TCI zurück
   * 
   * @param mixed $qvars werden als QueryString übergeben
   */
  public function getTabsURL(Array $qvars = array());
  
  /**
   * Gibt Informationen zurück die den aktuellen Zustand des Items eindeutig im Kontext des CMS beschreiben
   *
   * stellt man sich das TCI als URL vor ist die TabsId den Service die Resource Subresource und der Identifier des Items
   *
   * das Tupel: ('entities', 'text', 17, 'form') definiert: Entity "tiptoi\Entities\Text" mit dem Identifier (int) 17, Formular anzeigen / speichern
   * @return list($serviceName, $resourceName, $identifier, $action)
   */
  public function getTabsId();
  
  /**
   * Gibt den ServiceName Teil der TabsId zurück
   */
  public function getTabsServiceName();
  
  /**
   * Gibt den ResoureName Teil der TabsId zurück
   */
  public function getTabsResourceName();

  /**
   *
   * für Entities ist dies die ID
   * @return mixed
   */
  public function getTabsIdentifier();
  
  /**
   * Gibt den Action Teil der TabsId zurück
   *
   * @return string
   */
  public function getTabsAction();
  
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