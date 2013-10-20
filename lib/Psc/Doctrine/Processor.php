<?php

namespace Psc\Doctrine;

use Psc\Doctrine\Helper AS DoctrineHelper;
use Psc\Code\Code;
use Psc\Inflector;
use Psc\FE\Helper AS h;
use Webforge\Common\String AS S;
use Psc\SimpleObject;
use Closure;
use Psc\Data\ArrayCollection;
use Psc\Data\Set;
use Webforge\Types\Type;
use Doctrine\ORM\EntityManager;
use Webforge\Types\PersistentCollectionType;

/**
 * 
 * Der Processor hilft einem z. B. Collections in Objekten durch anderen Collections zu synchronisierne
 * 
 * (z. B. beim Auswerten der hydrierten Daten aus einem Formular mit einerm ComboDropBox Objekt)
 */
class Processor extends \Psc\System\LoggerObject {
  
  public $log;
  
  /**
   * @var Psc\Doctrine\Object
   */
  protected $entity;
  
  /**
   * @var Doctrine\ORM\EntityManager
   */
  protected $em;
  
  /**
   *
   * muss gesetzt sein wenn synchronizeCollections nicht false oder true ist sondern normal
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   * @var bool|string "normal" für den \Psc\Doctrine\CollectionSynchronizer string "old" für $this->synchronizeCollection()
   */
  protected $synchronizeCollections = false;
  
  /**
   * @var array
   */
  protected $onFieldTodos = array();
  
  
  public function __construct($entity, EntityManager $em, DCPackage $dc = NULL) {
    $this->entity = $entity;
    $this->em = $em;
    $this->dc = $dc;
    $this->setLogger(new \Psc\System\BufferLogger());
  }
  
  public function processSet(Set $set) {
    // weil der setter auch enabled sein soll, geht dies hier nicht im konstruktor zu überprüfen
    if (!($this->entity instanceof \Psc\Doctrine\Entity) && !($this->entity instanceof \Psc\Doctrine\Object)) {
      throw new \Psc\Exception('Entity ist kein bekanntes Interface');
    }
    
    foreach ($set->getMeta()->getTypes() as $field => $type) {
      $value = $set->get($field);
      
      if (isset($this->onFieldTodos[$field])) {
        call_user_func($this->onFieldTodos[$field], $this->entity, $field, $value, $type);
      
      } elseif ($type instanceof PersistentCollectionType) {
        $this->processCollectionField($this->entity, $field, $value, $type);
      
      } else {
        $this->logf("Setze '%s' auf %s", $field, Code::varInfo($value));
        $this->entity->callSetter($field, $value);
      }
    }
    
    return $this;
  }
  
  protected function processCollectionField($entity, $field, $value, $type) {
    if (is_array($value)) { // das drinlassen falls wir unten nativ callSetter aufrufen
      $value = new \Psc\Data\ArrayCollection($value);
    }
    
    if ($this->synchronizeCollections === 'normal') {
      $this->logf("Synchronizing Collection (normal way): '%s' ", $field);
      
      $this->synchronizeCollectionNormal($this->entity, $field, $value, $type);
    } elseif ($this->synchronizeCollections === 'old') {
      $this->logf("Synchronizing Collection (old way): '%s' ", $field);
      $this->synchronizeCollection($field, $value);
    } else {
      $entity->callSetter($field, $value);
    }
  }
  
  /**
   * Lets you override, what the processor does with a specific field
   * @param Closure $do function($entity, $field, $value, $type)
   */
  public function onProcessSetField($fieldName, \Closure $do) {
    $this->onFieldTodos[$fieldName] = $do;
    return $this;
  }
  
  /**
   * Synchronisiert Collections in einem Property des Entities
   *
   * Initialisiert einen CollectionSynchronizer, um das $entity->$field vom $type zu $toCollection zu synchronisieren
   */
  public function synchronizeCollectionNormal($entity, $field, $toCollection, Type $type) {
    if (!$this->dc) {
      throw new \RuntimeException('DCPackage muss für synchronizeCollectionNormal gesetzt sein');
    }
    
    $entityMeta = $this->dc->getEntityMeta($entity->getEntityName());
    $entityClassMeta = $entityMeta->getClassMetadata();
    $entityAssoc = $entityClassMeta->getAssociationMapping($field);
    $fromCollection = $entity->callGetter($field);
    
    if (!$entityClassMeta->isCollectionValuedAssociation($field)) {
      throw new \RuntimeException('Das Property '.$entity->getEntityName().'->'.$field.' ist keine Collection und kann somit nicht synchronisiert werden');
    }
      
    $adder = $remover = NULL;
    
    if ($entityClassMeta->isAssociationInverseSide($field)) {
      $otherMeta = $this->dc->getEntityMeta($entityAssoc['targetEntity']);
      $otherMetaClass = $otherMeta->getClassMetadata();
      $otherProperty = $entityAssoc['mappedBy'];
      $otherIsCollection = $otherMetaClass->isCollectionValuedAssociation($otherProperty);
      
      $addOrSet = $otherIsCollection
              ? 'add'.ucfirst($otherProperty)
              : 'set'.ucfirst($otherProperty);
      
      // da der CollectionSynchronizer nur die owningSide automatisch pflegt, injecten wir hier den adder und remover um die inverse Side zu pflegen
      $adder = function ($entity, $toEntity, $repository) use ($addOrSet) {
        $repository->persist($toEntity);
        $toEntity->$addOrSet($entity);
      };

      if ($otherIsCollection) {
        $remove = 'remove'.ucfirst($otherProperty);
        $remover = function ($entity, $toEntity, $repository) use ($remove) {
          $repository->persist($toEntity);
          $toEntity->$remove($entity);
        };
      } else {
        $set = 'set'.ucfirst($otherProperty);
        $remover = function ($entity, $toEntity, $repository) use ($set) {
          $repository->persist($toEntity);
          $toEntity->$set(NULL);
        };
      }
    }
    
    $synchronizer = CollectionSynchronizer::createFor($entity->getEntityName(), $field, $this->dc);
    $synchronizer->init($entity, $adder, $remover);
    return $synchronizer->process($fromCollection, $toCollection);
  }
  
  /**
   * @param  $entity
   * @chainable
   */
  public function setEntity($entity) {
    $this->entity = $entity;
    return $this;
  }
  
  /**
   * @return
   */
  public function getEntity() {
    return $this->entity;
  }
  
  /**
   * @param bool $synchronizeCollections
   */
  public function setSynchronizeCollections($synchronizeCollections) {
    $this->synchronizeCollections = $synchronizeCollections;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function getSynchronizeCollections() {
    return $this->synchronizeCollections;
  }
  
  /**
   * @param Psc\Doctrine\DCPackage $dc
   */
  public function setDoctrinePackage(DCPackage $dc) {
    $this->dc = $dc;
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage() {
    return $this->dc;
  }

  /**
   * Synchronisiert eine Collection eines Entities mit Entities in der Datenbank (deprecated)
   *
   * Diese Art der Synchroniserung ist deprecated, weil es mittlerweile ausgelagerte Funktionen dafür gibt
   * (siehe Psc\Doctrine\*Synchro*)
   * 
   * also es werden für die bestehenden Objekte in $entity->$collectionProperty die objekte gelöscht
   * die nicht in newCollection drin sind und die, die in $newCollection drin sind aber nicht in
   * $entity->$collectionProperty gelöscht
   * 
   * - Achtung(!): dies führt persist() auf den Entitys der Collection (also quasi andere Seite der Association) aus, wenn $this->entity nicht die Owning-Side der CollectionAssociation ist
   * - Dies führt persist für items in newCollection, die neu sind
   * 
   * Die Basis für eine erfolgreiche Synchronization ist eine $newCollection die wirklich die gleichen Objekte enthält, für die Updates gemacht werden sollen. Also vorsicht mit serialisierten Einträgen und clones
   * 
   * @param string $associationName der Name des Properties der Collection in $entity
   */
  public function synchronizeCollection($associationName, $newCollection) {
    if (!is_string($associationName)) {
      throw new \Psc\Exception('string erwartet: '.Code::varInfo($associationName));
    }
    
    /*
        $mapping     = $coll->getMapping();
        $targetClass = $this->_em->getClassMetadata($mapping['targetEntity']);
        $sourceClass = $this->_em->getClassMetadata($mapping['sourceEntity']);
        $id          = $this->_em->getUnitOfWork()->getEntityIdentifier($coll->getOwner());
    */
    if ($this->entity instanceof \Psc\Doctrine\Object) {
      $entityClass = $this->entity->getDoctrineMetadata(); // Sound
    } elseif ($this->entity instanceof \Psc\Doctrine\Entity) {
      $entityClass = $this->em->getClassMetadata($this->entity->getEntityName());
    } else {
      throw new \InvalidArgumentException('$this->entity ist von der Klasse: '.Code::getClass($this->entity).' und diese ist unbekannt');
    }
    
    $entityAssoc = $entityClass->getAssociationMapping($associationName); // speakers
    $owning = $entityAssoc['isOwningSide']; // true
    $targetClass = $this->em->getClassMetadata($entityAssoc['targetEntity']); // Metadata:Speaker
    
    if ($owning) {
      $entityProperty = $entityAssoc['inversedBy'];
      $collectionProperty = $entityAssoc['fieldName'];
    } else {
      $entityProperty = $entityAssoc['mappedBy'];
      $collectionProperty = $entityAssoc['fieldName'];
    }
    // entityProperty = sounds
    // collectionProperty = speakers
    
    $getter = Code::castGetter($collectionProperty); //getSpeakers
    $collection = $getter($this->entity); // $this->entity->getSpeakers()
    $collection = Code::castCollection($collection);
    $newCollection = Code::castCollection($newCollection);
    
    $this->log(sprintf("synchronizeCollection '%s'",$associationName));
    $this->log('in DBCollection:');
    $this->log(DoctrineHelper::debugCollection($collection));
    $this->log(NULL);
    $this->log('in FormCollection:');
    $this->log(DoctrineHelper::debugCollection($newCollection));
    $this->log(NULL);
    $this->log('Processing:');
    
    /*
      Wir synchronisieren hier auf der Owning Side oder Reverse Side
      jenachdem müssen wir auf der owning oder reverse side die add + remove funktionen aufrufen
    */
    if ($owning) {
      $remove = 'remove'.ucfirst(Inflector::singular($collectionProperty)); // removeSpeaker
      $add = 'add'.ucfirst(Inflector::singular($collectionProperty)); // addSpeaker
    } else {
      $remove = 'remove'.ucfirst(Inflector::singular($entityProperty)); // removeSound
      $add = 'add'.ucfirst(Inflector::singular($entityProperty)); // addSound
    }
        
    $logThis = Code::getClassName($entityClass->getName());
    // @TODO hier mit reflection prüfen
    //if (!->hasMethod($remove)) {
    //  throw new \Psc\Exception('Es gibt keine '.$remove.' Methode im Entity: '.$owningEntity);
    //}
    //if (!$this->entity->hasMethod($add)) {
    //  throw new \Psc\Exception('Es gibt keine '.$add.' Methode im Entity: '.$owningEntity);
    //}
    //
    foreach ($collection->deleteDiff($newCollection, ArrayCollection::COMPARE_OBJECTS) as $entity) {
      if ($owning) {
        $this->entity->$remove($entity); // $sound->removeSpeaker($speaker)
      } else {
        $this->em->persist($entity); // $speaker persist
        $entity->$remove($this->entity); // $speaker->removeSound($sound)
      }
    }
    
    foreach ($collection->insertDiff($newCollection, ArrayCollection::COMPARE_OBJECTS) as $entity) {
      
      if ($owning) {
        $this->entity->$add($entity);
        $this->em->persist($entity);
      } else {
        $entity->$add($this->entity);
        $this->em->persist($entity);
      }
    }
  }
}
?>