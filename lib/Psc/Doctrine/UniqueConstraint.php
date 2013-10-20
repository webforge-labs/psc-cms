<?php

namespace Psc\Doctrine;

use Psc\Data\SetMeta;

class UniqueConstraint extends \Psc\SimpleObject {
  
  /**
   * @var Psc\Data\SetMeta
   */
  protected $meta;
  
  /**
   * @var string
   */
  protected $name;

  /**
   * array Webforge\Types\Type[] $keys Schlüssel sind Namen der Felder des Unique-Constraints, Werte sind Psc\Data\Type\Type-Objekte
   */
  public function __construct($name, Array $keys) {
    $this->meta = new SetMeta($keys);
    $this->name = $name;
  }

  /**
   * @return string[]
   */
  public function getKeys() {
    return array_keys($this->meta->getTypes());
  }
  
  /**
   * @return Webforge\Types\Type
   */
  public function getKeyType($key) {
    return $this->meta->getFieldType($key);
  }
  
  /**
   * @param string $name
   * @chainable
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * Gibt einen Helper zurück, der die Daten des Unique-Keys für z. B. UniqueConstraintValidator vorbereitet
   *
   * der Helper hat soviele Parameter wie es Keys im UnqiueConstraint gibt. Die Reihenfolge der Argumente ist die Reihenfolge der Keys
   *
   * @see Test::testHelperGeneration()
   * @return Closure
   */
  public function getDataHelper() {
    $keys = $this->getKeys();
    
    $data = function () use ($keys) {
      $args = func_get_args();
      $argsNum = count($args);
      if ($argsNum > count($keys)+1 || $argsNum < count($keys)) {
        throw new \InvalidArgumentException('Es ist eine Anzahl von '.count($keys).'( + 1) Argumenten erwartet: '.$argsNum.' übergeben');
      }
      
      $keyData = array();
      foreach ($keys as $key) {
        $keyData[$key] = array_shift($args);
      }
      if (count($args) > 0) {
        $keyData['identifier'] = array_shift($args);
      }
      
      return $keyData;
    };
    
    return $data;
  }
}
