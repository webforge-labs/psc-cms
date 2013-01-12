<?php

namespace Psc\Doctrine;

use Psc\Inflector;
use Psc\CMS\EntityMeta;
use Psc\CMS\EntityPropertyMeta;
use Closure;

/**
 * Basis-Klasse für End-To-End Entity Synchronizers:
 *
 * bis jetzt gibt es den
 * PersistentCollectionSynchronizer: Synchronisiert eine PersistentCollection eines Entities mit einer unhydrierten Collection
 * CollectionSynchronizer:           Synchronisiert eine PersistentCollection eines Entities mit einer hydrierten Collection
 */
abstract class EntityCollectionSynchronizer extends \Psc\SimpleObject {

  /**
   * Die Meta des Entity in dem die Collection liegt
   *
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  /**
   * Der Name der Collection im Entity
   * 
   * @var string
   */
  protected $collectionName;
  
  /**
   * 
   * @var Psc\Doctrine\EventCollectionSynchronizer
   */
  protected $innerSynchronizer;
  
  public function __construct(EntityMeta $entityMeta, $collectionName, $innerSynchronizer) {
    $this->entityMeta = $entityMeta;
    $this->collectionName = $collectionName;
    $this->innerSynchronizer = $innerSynchronizer;
  }
  
  abstract public function process($fromCollection, $toCollection);
  
  /**
   * Initialisiert den Synchronizer mit dem $entity in dem $this->collectionName vorhanden ist
   *
   * dies er gibt dann die fromCollection
   */
  public function init(Entity $entity) {
    // check entity
    if ($entity->getEntityName() !== ($class = $this->entityMeta->getClass())) {
      throw new \InvalidArgumentException('Das übergebene Entity für diesen Synchronizer kann nur vom Typ: '.$class.' sein. Ein Entity des Typs '.$entity->getEntityName().' wurde übergeben');
    }
    
    // check relation
    $collectionMeta = $this->getCollectionPropertyMeta();
    if (!$collectionMeta->isRelation()) {
      throw new \InvalidArgumentException(
        sprintf('Das Property %s von %s ist keine Collection. Das Property muss ManyToMany oder OneToMany sein',
                $this->collectionName, $this->entityMeta->getClass())
      );
    }
  }
  
    /**
   * Hydriert ein Objekt aus $toCollection in ein Objekt des Universums von $fromCollection
   *
   * muss NULL zurückgeben, wenn es kein Objekt findet, ansonsten das Objekt
   * 
   */
  public function onHydrate(Closure $action) {
    $this->innerSynchronizer->onHydrate($action);
  }
  
  /**
   * Ein Objekt aus $toCollection wurde hydriert und soll in die $fromCollection eingefügt werden
   * @callbackParam mixed $fromObject
   */
  public function onInsert(Closure $action) {
    $this->innerSynchronizer->onInsert($action);
  }
  
  /**
   * Ein Objekt wurde aus $fromCollection entfernt (es wurde nicht in $toCollection hydriert)
   * 
   * delete bedeutet nur, dass das $fromObject in der Collection vorher war. Es bedeutet nicht, dass das $fromObject gelöschen werden soll (kann aber)
   * @callbackParam mixed $fromObject
   */
  public function onDelete(Closure $action) {
    $this->innerSynchronizer->onDelete($action);
  }
  
  /**
   *
   * update sagt nichts darüber aus, ob das Entity in der Collection war, sondern nur ob das Item im Universum existierte
   * @callbackParam mixed $toObject
   * @callbackParam mixed $fromObject
   */
  public function onUpdate(Closure $action) {
    $this->innerSynchronizer->onUpdate($action);
  }
  
  /**
   * @callbackParam mixed $fromObject
   */
  public function onHash(Closure $action) {
    $this->innerSynchronizer->onHash($action);
  }

  
  /**
   * @return list(string $add, string $remove)
   */
  protected function getRelationInterface(EntityPropertyMeta $collectionMeta = NULL) {
    $collectionMeta = $collectionMeta ?: $this->getCollectionPropertyMeta();
    $remove = 'remove'.ucfirst(Inflector::singular($collectionMeta->getName())); // removeTag
    $add = 'add'.ucfirst(Inflector::singular($collectionMeta->getName())); // addTag
    return array($add, $remove);
  }
  
  /**
   * @return Psc\CMS\EntityPropertyMeta
   */
  protected function getCollectionPropertyMeta() {
    return $this->entityMeta->getPropertyMeta($this->collectionName);
  }
}
?>