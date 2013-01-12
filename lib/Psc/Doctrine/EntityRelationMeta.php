<?php

namespace Psc\Doctrine;

use Psc\Code\Generate\GClass;
use Psc\Code\Code;
use Psc\Inflector;
use Psc\Data\Type\PersistentCollectionType;
use Psc\Data\Type\EntityType;

/**
 * 
 */
class EntityRelationMeta extends \Psc\SimpleObject {
  
  const COLLECTION_VALUED = 'collectionValued';
  
  const SINGLE_VALUED = 'singleValued';
  
  const TARGET = 'target';
  
  const SOURCE = 'source';
  
  /**
   * Die Klasse des Entities, das auf einer Seite der EntityRelation ist
   * 
   * @var Psc\Code\Generate\GClass
   */
  protected $gClass;
  
  /**
   * Der Name des Properties. Er muss ja nachdem singular oder plual sein
   * 
   * wird dieser nicht gesetzt, wird der name aus der gClass inferred
   * @var string
   */
  protected $propertyName;
  
  /**
   * Der Name des Parameters einer ReflectionInterface Methode
   *
   * Der singular propertyName sozusagen
   */
  protected $paramName;

  /**
   * @var string[] 0 => singular 1 => plural
   */
  protected $methodName;
  
  /**
   * Der Alias des KlassenNamen
   * 
   * @var string
   */
  protected $alias;
  
  /**
   * Der Typ der Seite der Relation (Target oder Source)
   * 
   * @var const
   */
  protected $type;
  
  /**
   * Typ des PropertyTyps (Collection oder Single Entity)
   * 
   * @var const self::COLLECTION_VALUED|self::SINGLE_VALUED
   */
  protected $valueType;
  
  /**
   * @var mixed
   */
  protected $identifier = 'id';
  
  /**
   * @var Psc\Inflector
   */
  protected $inflector;
  
  
  // der zweite parameter ist useless
  public function __construct(GClass $gClass, $type, $valueType = NULL, Inflector $inflector = NULL) {
    $this->setGClass($gClass);
    $this->setType($type);
    if (isset($valueType))
      $this->setValueType($valueType);
      
    $this->inflector = $inflector ?: new Inflector();
  }
  
  /**
   * Gibt den PHP-Property-Namen für die Entity-Klasse zurück
   */
  public function getPropertyName() {
    if (isset($this->propertyName))
      return $this->propertyName;
    
    if ($this->valueType === self::COLLECTION_VALUED) {
      // plural muss nach außen, sonst machen wir aus OIDs => oiDs , soll aber oids sein
      return Inflector::plural($this->inflector->propertyName($this->getName())); 
    } else {
      return $this->inflector->propertyName($this->getName());
    }
  }
  
  /**
   * Gibt den PHP Parameter Namen für das Relation Interface
   * 
   * das ist quasi der selbe wie der ProperytName jedoch ist dieser immer singular
   * 
   * OID => oid
   * OIDMeta => oidMeta
   * SomeClassName => someClassName
   * @return string immer in Singular und in property-kleinschreibweise
   */
  public function getParamName() {
    if (isset($this->paramName)) {
      return $this->paramName;
    }
    
    return $this->inflector->propertyName($this->getName());
  }
  
  /**
   * Gibt den Namen für die Methoden des Relation Interfaces zurück
   * 
   * z.B.               wird dann verwendet als
   * OID                addOID, removeOID, getOIDs
   * OIDMeta            addOIDMeta, setOIDMeta
   * SomeClassName      addSomeClassName, removeSomeClassName, setSomeClassNames
   * usw
   *
   * @return string
   */
  public function getMethodName($singularOrPlural = 'singular') {
    if (!isset($singularOrPlural)) {
      $singularOrPlural = $this->getValueType() === self::COLLECTION_VALUED ? 'plural' : 'singular';
    }
    
    if (!isset($this->methodName)) {
      $this->methodName = array(
        $this->getName(),
        Inflector::plural($this->getName())
      );
    }
    
    return $singularOrPlural === 'singular' ? $this->methodName[0] : $this->methodName[1];
  }

  /**
   * @param array $methodName list($singular, $plural)
   */
  public function setMethodName(Array $methodName) {
    $this->methodName = $methodName;
    return $this;
  }


  public function setPropertyName($name) {
    $this->propertyName = $name;
    return $this;
  }
  
  public function setParamName($name) {
    $this->paramName = $name;
    return $this;
  }
  
  /**
   * Setzt Param, Property und Method Name mit dem Alias
   *
   * @param string $name der Alias in Klassen-GroßSchreibweise z.b. HeadlineSound
   */
  public function setAlias($name) {
    $fc = mb_substr($name,0,1);
    if (!$fc === mb_strtoupper($fc)) {
      throw new \InvalidArgumentException('SetAlias braucht als Parameter den Alias als wäre er ein großgeschriebener KlassenName. '.Code::varInfo($name));
    }
    
    $this->alias = $name;
    return $this;
  }
  
  public function getAlias() {
    return $this->alias;
  }
  
  /**
   * Gibt den Typ des Properties in der Entity-Klasse zurück
   * 
   * @return Psc\Data\Type\Type
   */
  public function getPropertyType() {
    if ($this->valueType === self::COLLECTION_VALUED) {
      return new PersistentCollectionType($this->gClass);
    } else {
      return new EntityType($this->gClass);
    }
  }
  
  /**
   * @param Psc\Code\Generate\GClass $gClass
   */
  public function setGClass(GClass $gClass) {
    $this->gClass = $gClass;
    return $this;
  }
  
  /**
   * @return Psc\Code\Generate\GClass
   */
  public function getGClass() {
    return $this->gClass;
  }
  
  public function getFQN() {
    return $this->gClass->getFQN();
  }
  
  /**
   * Gibt den Namen der EntityRelationMeta zurück
   * 
   * @return string
   */
  public function getName() {
    if (isset($this->alias)) {
      return $this->alias;
    } else {
      return $this->gClass->getClassName();
    }
  }
  
  /**
   * @param string $type
   */
  public function setType($type) {
    Code::value($type, self::TARGET, self::SOURCE);
    $this->type = $type;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getType() {
    return $this->type;
  }
  
  /**
   * @param string $valueType
   */
  public function setValueType($valueType) {
    Code::value($valueType, self::COLLECTION_VALUED, self::SINGLE_VALUED);
    $this->valueType = $valueType;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getValueType() {
    return $this->valueType;
  }
  
  /**
   * @param mixed $identifier
   */
  public function setIdentifier($identifier) {
    $this->identifier = $identifier;
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function getIdentifier() {
    return $this->identifier;
  }
  
  /**
   * @return bool
   */
  public function isCollectionValued() {
    return $this->valueType === self::COLLECTION_VALUED;
  }

  /**
   * @return bool
   */
  public function isSingleValued() {
    return $this->valueType === self::COLLECTION_VALUED;
  }
}
?>