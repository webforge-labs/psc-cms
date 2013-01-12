<?php

namespace Psc\Code\Generate;

use \Reflector;

/**
 * @TODO es könnte sein, dass es Sinn macht die ClassReference im Hint noch mit der echten Klasse zu ersetzen
 * weil wir sonst vielleicht Probleme beim umbenennen bekommen
 */
class GParameter extends GObject {
  
  const UNDEFINED = '::.PscCodeGenerateDefaultIsUndefined.::'; // da wir von null unterscheiden müssen
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var bool
   */
  protected $optional;
  
  /**
   * @var bool
   */
  protected $allowsNull;
  
  /**
   * @var bool
   */
  protected $reference;
  
  /**
   * @var GClass
   */
  protected $hint;
  
  /**
   * @var bool
   */
  protected $array;
  
  /**
   *
   * Es ist nicht safe nach default === NULL abzufragen ob der Parameter optional ist. => isOptional ist nur verlässlich
   * @var mixed wenn dies NULL Ist kann das bedeuten, dass es "undefined" ist oder === NULL ist dies richtet sich dann nach $optional
   */
  protected $default;
  
  public function __construct($name = NULL, $hint = NULL, $default = self::UNDEFINED, $reference = FALSE) {
    $this->setName($name);
    $this->setHint($hint);
    $this->setDefault($default);
    $this->reference = (bool) $reference;
  }
  
  public function elevate(Reflector $reflector) {
    $this->reflector = $reflector;
    $this->elevateValues(
      array('allowsNull','allowsNull'),
      array('array','isArray'),
      array('reference','isPassedByReference'),
      'name',
      array('optional','isOptional')
    );
    
    try {
      if (($hint = $this->reflector->getClass()) != NULL) {
        //$this->hint = GClass::reflectorFactory($hint); // das macht zu tiefe rekursion, deshalb erstellen wir hier nur die gclass
        $this->hint = new GClassReference($hint->getName());
      }
    } catch (\ReflectionException $e) {
      throw new ReflectionException('Reflection kann den Hint für den Parameter '.$this->name.' nicht laden: '.$e->getMessage(), 0, $e);
    }
    
    if ($this->isOptional()) {
      try {
        $this->elevateValues(
          array('default','getDefaultValue')
        );
      } catch (\ReflectionException $e) {
        if ($e->getMessage() === 'Cannot determine default value for internal functions') {
          $this->setDefault(NULL);
        }
      }
    }
  }
  
  public function php($useFQNHints = FALSE) {
    $php = NULL;
    
    // Type Hint oder Array
    if ($this->isArray()) {
      $php .= 'Array ';
    } elseif (($c = $this->getHint()) != NULL) {
      if ($useFQNHints) {
        $php .= $c->getName().' ';
      } else {
        $php .= $c->getClassName().' ';
        // @TODO das hier macht aus den FQNs immer was ohne \ am Anfang,
        // und hat nie den Namespace dabei
        // wir müssen dann die imports so anpassen, dass das auch hinhaut ...
      }
    }
    
    // name
    $php .= ($this->isReference() ? '&' : NULL).'$'.$this->name;
    
    // optional
    if ($this->isOptional()) {
      $php .= ' = ';
      if (is_array($this->default) && count($this->default) == 0) {
        $php .= 'array()';
      } else {
        $php .= $this->exportArgumentValue($this->default); // das sieht scheise aus
      }
    }
    
    return $php;
  }
  
  public function setHint($hint = NULL) {
    if ($hint != NULL && !($hint instanceof GClass)) {
      if (mb_strtolower($hint) === 'array') {
        return $this->setArray(TRUE);
      }
      
      $hint = new GClass($hint); // hier keine factory lieber, da wir sonst immer die Klasse physikalisch autoloaden müssen
    }
    
    if ($hint != NULL) {
      $this->array = FALSE;
    }
    
    $this->hint = $hint;
    return $this;
  }
  
  public function setArray($bool) {
    $this->array = (bool) $bool;
    if ($this->array) $this->setHint(NULL);
    return $this;
  }
  
  public function isOptional() {
    return $this->optional;
  }
  
  public function isReference() {
    return $this->reference;
  }

  public function isArray() {
    return $this->array;
  }
  
  public function allowsNull() {
    return $this->null;
  }

  public function setDefault($default) {
    if ($default === self::UNDEFINED) { // regression: nicht == nehmen denn das ist truly mit 0. Somit würde nicht 0 als default-wert gehen!
      $this->default = NULL;
      $this->optional = FALSE;
    } else {
      $this->default = $default;
      $this->optional = TRUE;
    }
    return $this;
  }
  
  public function setName($name) {
    $this->name = ltrim($name,'$');
    return $this;
  }
  
  /**
   * @return string ohne $ davor
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * @return mixed dies gibt auch NULL zurück wenn isOptional FALSE ist! also aufpassen
   */
  public function getDefault() {
    return $this->default;
  }
  
  public function setOptional($bool) {
    $this->optional = (bool) $bool;
    if (!$this->optional) $this->default = NULL;
    return $this;
  }
}