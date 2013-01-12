<?php

namespace Psc\Doctrine;

use Psc\DataInput;
use Psc\Data\Type\TypeMatcher;
use Psc\Data\Type\TypeExpectedException;

class UniqueConstraintValidator extends \Psc\SimpleObject {
  
  protected $index;
  
  protected $uniqueConstraints = array();
  
  protected $typeMatcher;
  
  public function __construct(UniqueConstraint $constraint, TypeMatcher $typeMatcher = NULL) {
    $this->index = new \Psc\DataInput;
    $this->addUniqueConstraint($constraint);
    $this->typeMatcher = $typeMatcher ?: new TypeMatcher();
  }
  
  public function addUniqueConstraint(UniqueConstraint $constraint) {
    $this->uniqueConstraints[$constraint->getName()] = $constraint;
    return $this;
  }

  /**
   * Überprüft die Daten anhand der Constraints und fügt die Daten zum Index hinzu
   *
   * Schmeisst eine Exception wenn die daten bereits im Index sind
   * diese Exception wird mit duplicateIdentifier gesetzt, wenn in $data der key 'identifier' für den vorigen Index-Eintrag gesetzt war
   */
  public function process(Array $data) {
    foreach ($this->uniqueConstraints as $constraint) {
      
      $constraintValue = array();
      foreach ($constraint->getKeys() as $key) { // da wir diese reihenfolge nehmen, ist diese immer gleich
        $type = $constraint->getKeyType($key);
        
        if (!array_key_exists($key, $data)) {
          throw \Psc\Data\Type\WrongDataException::create("In Data ist der Schlüssel '%s' nicht vorhanden. Erwartet: '%s'", $key, $type->getName());
        }
        $value = $data[$key];
        
        if (!$this->typeMatcher->isTypeof($value, $type)) {
          throw TypeExpectedException::build("Data %s für Key: '%s' sollte vom Type '%s' sein", \Psc\Code\Code::varInfo($value), $key, $type->getName())
                                      ->set('expectedType', $type)
                                      ->end();
        }
        
        $constraintValue[$key] = $value;
      }
      
      // schlüssel darf es nicht geben, wert nicht gesetzt kann es nicht geben
      if (($indexEntry = $this->index->get($constraintValue, FALSE, DataInput::THROW_EXCEPTION)) === FALSE) {
        // ok: nicht im index => zum index hinzufügen
        $this->index->set($constraintValue, array_key_exists('identifier', $data) ? $data['identifier'] : TRUE);
        
      } else {
        throw UniqueConstraintException::factory($constraint->getName(),
                                                 $this->getStringValue($constraintValue),
                                                 $constraintValue,
                                                 !is_bool($indexEntry) ? $indexEntry : NULL // kann der identifier sein
                                                 );
      }
    }
  }
  
  
  /**
   * Fügt mehrere Einträge zum Unique-Index hinzu
   *
   * ist z. B. dafür nötig, um den Index mit der Datenbank zu synchronisieren.
   * @param Array $dataRows jeder Eintrag ist ein Parameter für $this->process()
   * @chainable
   */
  public function updateIndex(UniqueConstraint $constraint, Array $dataRows) {
    if (!array_key_exists($constraint->getName(), $this->uniqueConstraints)) {
      throw new \InvalidArgumentException(sprintf("Constraint '%s' wurde noch nicht mit addConstraint() hinzugefügt'",$constraint->getName()));
    }
    
    foreach ($dataRows as $data) {
      // hierbei können natürlich auch UniqueConstraintExceptions auftreten, diese wollen wir aber nicht abfangen, weil wir davon ausgehen, dass meistens diese Funktion dafür genutzt wird, den Index aus der Datenbank zu füllen (die ja konsistent in sich, ist)
      
      $this->process($data);
    }
    return $this;
  }
  
  protected function getStringValue($constraintValue) {
    return implode('-', $constraintValue);
  }
  
  ///**
  // * @param Array $uniqueConstraints
  // * @chainable
  // */
  //public function setUniqueConstraints(Array $uniqueConstraints) {
  //  $this->uniqueConstraints = $uniqueConstraints;
  //  return $this;
  //}

  /**
   * @return Array
   */
  public function getUniqueConstraints() {
    return $this->uniqueConstraints;
  }
}
?>