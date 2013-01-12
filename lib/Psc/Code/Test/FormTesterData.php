<?php

namespace Psc\Code\Test;

use \Psc\Code\Code;
use \Psc\Doctrine\EntityDataRow;

/**
 * Die Daten für den FormTester
 *
 * können in provide()-Funktionen erzeugt werden
 *
 * $formRow sind die Daten, die in die Form eingefügt werden sollen
 * $expectedRow sind die Daten des Entities nachdem es durch den Controller oder sonstwas aufruf gespeichert wurden
 */ 
class FormTesterData extends \Psc\Object {
  
  const FORM = 'form';
  const EXPECTED = 'expected';
  
  /**
   * @var EntityDataRow
   */
  protected $formRow;

  /**
   * @var EntityDataRow
   */
  protected $expectedRow;
  
  public function __construct(EntityDataRow $formRow, EntityDataRow $expectedRow) {
    $this->formRow = $formRow;
    $this->expectedRow = $expectedRow;
  }
  
  /**
   * Gibt die Value aus $formRow zurück
   *
   */
  public function getFormValue($property) {
    return $this->formRow->has($property) ? $this->formRow->getData($property) : NULL; 
  }

  /**
   * Gibt die Value aus $expectedRow zurück ansonsten aus $formRow
   *
   * sind beide nicht gesetzt wird NULL zurückgegeben
   */
  public function getExpectedValue($property) {
    return $this->expectedRow->has($property)
      ? $this->expectedRow->getData($property)
      : $this->formRow->getData($property);
  }
  
  public function setExpectedValue($property, $value) {
    $this->expectedRow->add($property,$value);
  }
  
  public function getData($type) {
    Code::value($type, self::FORM, self::EXPECTED);
    if ($type == self::FORM) {
      return $this->formRow;
    } else {
      return $this->expectedRow;
    }
  }

  /**
   * @return stdClass|mixed wenn property gesetzt wird die value von property ansonsten eine stdClass mit den schlüsseln als property Name
   */
  public function getExpected($property = NULL) {
    return $this->expectedRow->getData($property);
  }

  /**
   * @return stdClass|mixed wenn property gesetzt wird die value von property ansonsten eine stdClass mit den schlüsseln als property Name
   */
  public function getForm($property = NULL) {
    return $this->formRow->getData($property);
  }
}
?>