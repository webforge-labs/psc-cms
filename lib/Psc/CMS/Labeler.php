<?php

namespace Psc\CMS;

use Webforge\Common\String AS S;
use Psc\Preg;

/**
 * Erstellt / encapsulates Labels und Meta Informationen für Komponenten
 *
 * @TODO internationalization
 */
class Labeler extends \Psc\SimpleObject {
  
  protected $customLabels = array();
  
  protected $commonLabels = array(
    'email'=>'E-Mail',
    'firstName'=>'Vorname',
    'date'=>'Datum',
    'recipient'=>'Empfänger',
    'amount'=>'Betrag',
    'title'=>'Titel',
    'color'=>'Farbe'
  );
  
  public function getLabel($identifier) {
    if (array_key_exists($identifier, $this->customLabels)) {
      return $this->customLabels[$identifier];
    }
    
    return $this->guessLabel($identifier);
  }
  
  public function guessLabel($identifier) {
    if (array_key_exists($identifier, $this->commonLabels)) {
      return $this->commonLabels[$identifier];
    }
    
    // camelCase => camel Case
    $identifier = Preg::replace($identifier, '/([a-z])([A-Z])/', '$1 $2');
    
    // camel Case => Camel Case
    return S::ucfirst($identifier);
  }
  
  public function addLabelMapping($identifier, $label) {
    $this->customLabels[$identifier] = $label;
    return $this;
  }
  
  public function label($identifier, $label) {
    return $this->addLabelMapping($identifier, $label);
  }
  
  public function getCustomLabels() {
    return $this->customLabels;
  }
  
  public function getCommonLabels() {
    return $this->commonLabels;
  }
}
?>