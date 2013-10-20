<?php

namespace Psc\CMS;

use Webforge\Types\Type;

/**
 * Eine Component ist ein Teil eines Formulars der die Darstellung von Daten im Formular steuert
 *
 * @TODO ich glaube wir brauchen hier eine Steuerung dafür, wann die Componente schon "umgewandelt" wurde. Mit umgewandelt meine ich, dass die Componente schon als HTML zurückgeben wurde. Denn nicht alles lässt sich in der Componente so schön abstrahieren, dass alle Setter auf der Componente immer auch das HTML modifizieren. Das Problem hatte ich schon an mehreren Stellen bei den HTML-Form Dingern
 * Am Besten wäre vielleicht sowas wie morph() und danach ist dann Schluss mit ein paar Settern.
 */
interface Component extends \Psc\Form\Item, \Webforge\Types\Adapters\Component {
  
  /**
   * Sollte aufgerufen werden, sobald die Componente Form-Name, Value und Label gesetzt hat
   */
  public function init();
  
  /**
   * Gibt einen eindeutigen Namen im Formular für die Komponente zurück
   *
   * dies ist z. B. bei einem TextFeld im HTML-Formular der Inhalt des Attributes "name" von "<input />"
   * @return string
   */
  public function getFormName();
  
  /**
   * Gibt das HTML (als Struktur von HTMLTags) für die Komponente zurück
   *
   */
  public function getHTML();

  /**
   * @chainable
   */
  public function setFormName($name);
  
  /**
   * @chainable
   */
  public function setFormLabel($label);
  
  /**
   * @chainable
   */
  public function setFormValue($value);
  
  
  /**
   * Setzt den Inhalt der Componente aus den "RAW"-Daten eines Formulars
   *
   * wird z.B. vom ComponentValidator aufgerufen, bevor getValidatorRule() aufgerufen wird, um die RAW-Value in die richtige Value umzuwandeln
   * die Componente erhält alle Felder, die sie als input definiert hat
   * (im Moment noch alle des Formulars)
   * der ComponentsValidator kann benutzt werden um Abhängigkeiten aufzulösen
   */
  public function onValidation(\Psc\Form\ComponentsValidator $validator);
  
  /**
   * @return bool
   */
  public function hasHint();
  
  public function setHint($hint = NULL);
  
  public function getHint();

  
  public function setType(Type $type);
  
  /**
   * @return Psc\Data\Type\Type
   */
  public function getType();

  /**
   * Gibt den Namen der Componente zurück
   *
   * also TextField oder EmailPicker oder FormComboBox oder FormComboDropBox usw
   */
  public function getComponentName();
  
  /**
   * @return bool
   */
  public function hasValidatorRule();
  
  /**
   * @return string|Psc\Form\ValidatorRule
   */
  public function getValidatorRule();

  /**
   * @param string|Psc\Form\ValidatorRule $validatorRule kann auch sowas wie "Id" oder "IdValidatorRule" sein. Wird der erste Parameter von ComponentRuleMapper->createRule()
   * @chainable
   */
  public function setValidatorRule($validatorRule);
}
