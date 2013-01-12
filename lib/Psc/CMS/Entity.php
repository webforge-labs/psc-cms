<?php

namespace Psc\CMS;

/**
 * Entity-Interface für alle CMS High-Level Funktionen
 *
 * - Das CMS Entity ist ein Entity welches in der Datenbank abgespeichert werden kann (\Psc\Doctrine\Entity)
 * - Form Item wird für ein paar Base-HTML Klassen gebraucht (fast deprecated)
 * - Exportable exportiert ein Item für z. B. JSON oder Caches etc
 *
 * eine Base-Klasse für dieses Interface ist Psc\CMS\AbstractEntity (ersetzt Psc\Doctrine\Object)
 */
interface Entity extends \Psc\Doctrine\Entity, \Psc\Data\Exportable {

  const CONTEXT_DEFAULT = EntityMeta::CONTEXT_DEFAULT;
  const CONTEXT_ASSOC_LIST = EntityMeta::CONTEXT_ASSOC_LIST;
  const CONTEXT_LINK = EntityMeta::CONTEXT_LINK;
  const CONTEXT_RIGHT_CONTENT = EntityMeta::CONTEXT_RIGHT_CONTENT;
  const CONTEXT_TAB = EntityMeta::CONTEXT_TAB;
  const CONTEXT_BUTTON = EntityMeta::CONTEXT_BUTTON;
  const CONTEXT_FULL = EntityMeta::CONTEXT_FULL;
  const CONTEXT_AUTOCOMPLETE = EntityMeta::CONTEXT_AUTOCOMPLETE;
  const CONTEXT_GRID = EntityMeta::CONTEXT_GRID;

  
  /**
   * Gibt Meta-Daten für alle Properties des Entities zurück
   *
   * Aus diesen Properties-Metas werden z. B. die automatischen Componenten fürs CMS generiert
   * @return Psc\Data\SetMeta
   */
  public static function getSetMeta();
  
  
  /**
   * Gibt das Label des Entities für einen bestimmten Context zurück
   *
   * Contexte sind in in self::CONTEXT_* Konstanten
   * dies sind Labels für den GUI
   * @var string
   */
  public function getContextLabel($context = self::CONTEXT_DEFAULT);

  /**
   * @see Psc\CMS\Item\Buttonable
   */
  public function getButtonRightIcon($context = self::CONTEXT_DEFAULT);
  
  /**
   * @see Psc\CMS\Item\Buttonable
   */
  public function getButtonLeftIcon($context = self::CONTEXT_DEFAULT);

}
?>