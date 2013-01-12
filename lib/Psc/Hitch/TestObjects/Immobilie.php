<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Immobilie extends \Psc\XML\Object {
  
  /**
   * @xml\XmlType
   */
  protected $objektkategorie;

  /**
   * @xml\XmlType
   */
  protected $geo;

  /**
   * @xml\XmlType
   */
  protected $kontaktperson;

  /**
   * @xml\XmlType
   */
  protected $preise;

  /**
   * @xml\XmlType
   */
  protected $flaechen;

  /**
   * @xml\XmlType
   */
  protected $freitexte;
  
  /**
   * @xml\XmlList(name="anhang",wrapper="anhaenge",type="Anhang")
   */
  protected $anhaenge;

  /**
   * @xml\XmlElement(name="verwaltung_techn",type="\Psc\Hitch\TestObjects\VerwaltungTechn")
   */
  protected $verwaltungTechn;
  
}
?>