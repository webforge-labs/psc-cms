<?php

namespace Psc\Data;

use Psc\Data\Type\ObjectType;
use Psc\Data\Type\ArrayType;
use Psc\Data\Type\StringType;
use Psc\Data\Type\FloatType;
use Psc\Data\Type\IntegerType;
use Psc\Data\Type\BooleanType;
use Psc\Data\Type\CollectionType;
use Psc\Data\Type\WalkableHintType;
use Psc\Data\Type\DateTimeType;

use Psc\Data\Type\Type;
use Psc\Data\Type\Inferrer;

use Psc\Code\Code;

class Walker extends \Psc\SimpleObject {
  
  protected $walkable;
  
  protected $typeInferrer;
  
  public function __construct(Inferrer $inferrer = NULL) {
    $this->typeInferrer = $inferrer ?: new Inferrer();
  }
  
  public function walkWalkable(Walkable $object) {
    $this->walkable = $object;

    $walkedFields = array();
    foreach ($object->getWalkableFields() as $field => $value) {
      $walkedFields[$field] = $this->walkField($field, $value, $object->getWalkableType($field));
    }
    
    return $this->decorateWalkable($object, $walkedFields);
  }
  
  public function walkField($field, $value, Type $type) {
    $walkedValue = $this->walk($value, $type);
   
    return $this->decorateField($field, $walkedValue, $type);
  }
  
  public function walk($value, Type $type) {
    // use oben nicht vergessen
    
    if ($value instanceof Walkable) {
      return $this->walkWalkable($value);
    } elseif ($type instanceof CollectionType) {
      return $this->walkCollection($value, $type);
    } elseif ($type instanceof ObjectType && $type->getClassFQN() === 'Psc\Data\Walkable') {
      return $this->walkWalkable($value);
    } elseif ($type instanceof StringType) {
      return $this->walkString($value);
    } elseif ($type instanceof FloatType) {
      return $this->walkFloat($value);
    } elseif ($type instanceof ArrayType) {
      return $this->walkArray($value, $type);
    } elseif ($type instanceof IntegerType) {
      return $this->walkInteger($value);
    } elseif ($type instanceof BooleanType) {
      return $this->walkBoolean($value);
    } elseif ($type instanceof DateTimeType) {
      return $this->walkDateTime($value);
    } elseif ($type instanceof WalkableHintType) {
      $hint = 'walk'.ucfirst($type->getWalkableHint());
      return $this->$hint($value);
    }
    
    throw new Exception('Kein Walk für '.$type->getName().' ['.Code::getClass($type).'] definiert');
  }
  
  public function walkArray(Array $array, Type $arrayType = NULL) {
    $walkedEntries = array();
    /* 1. Fall: wir kennen den Inhalt des Arrays (er hat einen Typ) */
    if (isset($arrayType) && $arrayType->isTyped()) {
      $entryType = $arrayType->getType();
    
      foreach ($array as $key => $entry) {
        $walkedEntries[$key] = $this->walkArrayEntry($entry, $entryType, $key, $array, $arrayType);
      }
    
    } else {
      /* 2. Fall wir kennen den Inhalt nicht */
      if (!isset($arrayType))
        $arrayType = new ArrayType();
      
      foreach ($array as $key => $entry) {
        $entryType = $this->inferArrayEntryType($entry, $key, $array, $arrayType);
        
        // damit walkArrayEntry hier volle power hat rufen wir direkt mit $entry auf nicht mit $this->walk($entry)
        $walkedEntries[$key] = $this->walkArrayEntry($entry, $entryType, $key, $array, $arrayType);
      }
    }
    
    return $this->decorateArray($walkedEntries, $arrayType);
  }
  
  public function walkCollection($collection, Type $collectionType = NULL) {
    $walkedItems = array();
    /* 1. Fall: wir kennen den Inhalt des Arrays (er hat einen Typ) */
    if (isset($collectionType) && $collectionType->isTyped()) {
      $itemType = $collectionType->getType();
    
      foreach ($collection->toArray() as $key => $item) {
        $walkedItems[$key] = $this->walkCollectionItem($item, $itemType, $key, $collection, $collectionType);
      }
    
    } else {
      /* 2. Fall wir kennen den Inhalt nicht */
      if (!isset($collectionType))
        $collectionType = $this->inferType($collection);
      
      foreach ($collection->toArray() as $key => $item) {
        $itemType = $this->inferCollectionItemType($item, $key, $collection, $collectionType);
        
        // damit walkArrayEntry hier volle power hat rufen wir direkt mit $entry auf nicht mit $this->walk($entry)
        $walkedItems[$key] = $this->walkCollectionItem($item, $itemType, $key, $collection, $collectionType);
      }
    }
    
    return $this->decorateCollection($walkedItems, $collectionType);
  }
  
  public function inferArrayEntryType($entry, $key, Array $array, Type $arrayType) {
    // beim Walkable nachfragen?
    //if (isset($this->walkable))
      //$this->walkable->getWalkableArrayEntryType($entry, $key, $array, $arrayType);
      
    return $this->inferType($entry);
  }

  public function inferCollectionItemType($item, $key, $collection, Type $collectionType) {
    return $this->inferType($item);
  }
  
  public function inferType($value) {
    return $this->typeInferrer->inferType($value); // kann exception schmeissen
  }
  
  /**
   * Wird aufgerufen für einen Eintrag in einem Array (kann auch unterverschachtelt sein)
   *
   * Entry kann noch ein nicht-Basis-Typ sein und kann dann mit $this->walk($entry, $entryType) aufgelöst werden
   */
  public function walkArrayEntry($entry, Type $entryType, $key, Array $array, Type $arrayType) {
    $walkedEntry = $this->walk($entry, $entryType); // demo zwecke (wir vergessen hier was mit key zu machen)
    return $this->decorateArrayEntry($walkedEntry, $key);
  }

  /**
   * Wird aufgerufen für einen Eintrag in einer collection (kann auch unterverschachtelt sein)
   *
   * item kann noch ein nicht-Basis-Typ sein und kann dann mit $this->walk($item, $itemType) aufgelöst werden
   */
  public function walkCollectionItem($item, Type $itemType, $key, $collection, Type $collectionType) {
    $walkedItem = $this->walk($item, $itemType); // demo zwecke (wir vergessen hier was mit key zu machen)
    return $this->decorateCollectionItem($walkedItem, $key);
  }

  public function walkString($string) {
    return $this->decorateString($string);
  }

  public function walkFloat($string) {
    return $this->decorateFloat($string);
  }

  public function walkInteger($integer) {
    return $this->decorateInteger($integer);
  }

  public function walkBoolean($bool) {
    return $this->decorateBoolean($bool);
  }

  public function walkDateTime($datetime) {
    if ($datetime instanceof \DateTime) {
      $datetime = new \Psc\DateTime\DateTime($datetime);
    }
    
    return $this->walkWalkable($datetime);
  }
  
  /**
   * @param Psc\Data\Type\Inferrer $inferrer
   * @chainable
   */
  public function setInferrer(Inferrer $inferrer) {
    $this->inferrer = $inferrer;
    return $this;
  }

  /**
   * @return Psc\Data\Type\Inferrer
   */
  public function getInferrer() {
    return $this->inferrer;
  }
  
  
  
  /* partial INTERFACE WalkerDecorator */
  public function decorateString($string) {
    return $string;
  }

  public function decorateInteger($integer) {
    return $integer;
  }

  public function decorateFloat($float) {
    return $float;
  }

  public function decorateBoolean($bool) {
    return $bool ? 'TRUE' : 'FALSE';
  }

  public function decorateArrayEntry($walkedEntry, $key) {
    return $walkedEntry;
  }

  public function decorateCollectionItem($walkedItem, $key) {
    return $walkedItem;
  }

  public function decorateCollection($walkedItems, $collectionType) {
    return $walkedItems;
  }
  
  public function decorateArray($walkedEntries, $arrayType) {
    return $walkedEntries;
  }
  
  public function decorateField($field, $walkedValue, $fieldType) {
    return $walkedValue;
  }
  
  public function decorateWalkable($walkable, $walkedFields) {
    return $walkedFields;
  }
}
?>