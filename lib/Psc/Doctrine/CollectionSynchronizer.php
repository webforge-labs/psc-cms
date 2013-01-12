<?php

namespace Psc\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Collections\Collection;
use Psc\Code\Code;
use Closure;
use Psc\CMS\EntityMeta;

/**
 * Der Classic CollectionSynchronizer
 *
 * Synchronisiert eine PersistentCollection eines Entities mit einer fertigen komplett hydrierten Collection
 */
class CollectionSynchronizer extends EntityCollectionSynchronizer {
  
  public function __construct(EntityMeta $entityMeta, $collectionName, EntityRepository $collectionRepository, GenericCollectionSynchronizer $innerSynchronizer = NULL) {
    $this->collectionRepository = $collectionRepository;
    parent::__construct($entityMeta, $collectionName, $innerSynchronizer ?: new GenericCollectionSynchronizer());
  }
  
  public static function createFor($entityClass, $collectionPropertyName, DCPackage $dc) {
    $entityMeta = $dc->getEntityMeta($entityClass);
    $property = $entityMeta->getPropertyMeta($collectionPropertyName);
    $collectionClass = $property->getRelationEntityClass();

    return new static(
      $entityMeta,
      $collectionPropertyName,
      $dc->getRepository($collectionClass->getFQN())
    );
  }

  /**
   * Kompiliert den Synchronizer und initialisiert
   *
   * wenn angegeben $add angegben wird, wird diese Closure mit $entity (der Parameter der init() Funktion) und $otherEntity aufgerufen
   * bei $add ist $otherEntity ein Entity aus der $toCollection welches nicht in der $fromColleciton ist
   * bei $remove ist $otherEntity ein Entity aus der $fromCollection welches nicht in der $toCollection ist
   * als dritter parameter wird das $repository des Entities in der Collection übergeben (also nicht das von $entity)
   *
   * die Standard-Adder sind nur für den Fall, dass diese Klasse auch die owning Side ist
   * sie rufen also $entity->addXXX($toEntity) auf
   * bzw
   * $entity->removeXXX($fromEntity)
   * 
   * @param Closure $adder function ($entity, $toEntity, $collectionRepository)  persist nicht vergesen!
   * @param Closure $remover function ($entity, $fromEntity, $collectionRepository) 
   */
  public function init(Entity $entity, Closure $adder = NULL, Closure $remover = NULL) {
    parent::init($entity);
    
    list($add, $remove) = $this->getRelationInterface();
    
    if (!$adder) {
      $adder = function ($entity, $toEntity, $repository) use ($add) {
        $repository->persist($toEntity);
        $entity->$add($toEntity);
      };
    }
    
    if (!$remover) {
      $remover = function ($entity, $fromEntity, $repository) use ($remove) {
        $repository->persist($fromEntity);
        $entity->$remove($fromEntity);
      };
    }
    
    $repository = $this->collectionRepository;
    
    // insert: in $toCollection gibt es ein $toEntity, welches noch nicht in $fromCollection war
    $this->innerSynchronizer->onInsert(function ($toEntity) use ($repository, $entity, $adder) {
      $adder($entity, $toEntity, $repository);
    });
    
    // update bzw merge: das $toEntity war bereits in der $fromCollection und ist in der $toCollection
    $this->innerSynchronizer->onUpdate(function ($toEntity) use ($repository, $entity, $adder) {
      $adder($entity, $toEntity, $repository, $isUpdate = TRUE);
    });
    
    // delete: das $fromEntity war vorher in der $fromCollection und ist nicht mehr in der $toCollection
    $this->innerSynchronizer->onDelete(function (Entity $fromEntity) use ($entity, $remover, $repository) {
      $remover($entity, $fromEntity, $repository);
      // wenn $fromEntity 0 verknüpfungen hat, könnte man es hier auch löschen, dies macht aber das relationinterface
    });
    
    return $this;
  }
  
  public function process($fromCollection, $toCollection) {
    // der genericCollectionSynchronizer braucht ArrayCollections
    $fromCollection = Code::castCollection($fromCollection);
    $toCollection = Code::castCollection($toCollection);
    
    // die defaults sind: compare nach object, hashe nach identifier, und die sind prima für uns
    $this->innerSynchronizer->process($fromCollection, $toCollection); 
  }
  
  /**
   * @param Psc\Doctrine\EventCollectionSynchronizer $innerSynchronizer
   */
  public function setInnerSynchronizer(EventCollectionSynchronizer $innerSynchronizer) {
    $this->innerSynchronizer = $innerSynchronizer;
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\EventCollectionSynchronizer
   */
  public function getInnerSynchronizer() {
    return $this->innerSynchronizer;
  }
}
?>