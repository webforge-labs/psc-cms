<?php

namespace Psc\Doctrine;

use \Psc\Doctrine\Helper as DoctrineHelper;

class EntityReference extends \Psc\Object {
  
  protected $identifier;
  protected $entityName;
  
  protected $entity; // kann gesetzt sein, aber auch nicht
  
  protected $em;
  
  public function __construct($entityName, $identifier, \Doctrine\ORM\EntityManager $em) {
    $this->identifier = $identifier;
    $this->entityName = DoctrineHelper::getEntityName($entityName);
    $this->em = $em;
  }
  
  /**
   * Gibt das Entity zurück, wenn es gefunden werden kann
   */
  public function find() {
    return $this->em->find($this->entityName, $this->identifier);
  }
  
  /**
   * Schmeisst eine Exception wenn die Reference nicht aufgelöst werden kann
   */
  public function hydrate() {
    if (!isset($this->em)) {
      $e = new EntityReferenceHydrationexception('Konnte Reference: '.$this->getEntityName().':'.$this->getIdentifier().' nicht auflösen. Da der EntityManager nicht gesetzt ist.');
      $e->reference = $this;
      throw $e;
    }
    
    try {
      
      return $this->em->getRepository($this->entityName)->hydrate($this->identifier);
    } catch (\Psc\Doctrine\EntityNotFoundException $e) {
      $e = new EntityReferenceHydrationexception('Konnte Reference: '.$this->getEntityName().':'.$this->getIdentifier().' nicht auflösen. ');
      $e->reference = $this;
      throw $e;
    }
  }
  
  public function getEntity() {
    if (isset($this->entity))
      return $this->entity;
    else 
      return $this->hydrate();
  }
  
  public function isNew() {
    if (!isset($this->entity) && !empty($this->identifier)) return FALSE;
    
    return $this->isPersisted();
  }
  
  public function setEntity(\Psc\Doctrine\Entity $entity) {
    $this->identifier = $entity->getIdentifier();
    $this->entity = $entity;
    $this->entityName = $entity->getEntityName();
  }
  
  /**
   * wenn unser Entity keine ID hat und als persist() gekennzeichnet ist
   * speichern wir dieses zuerst und serialisieren dann erst, damit eine ID serialisiert wird
   *
   * Achtung(!): dies kann den EntityManager des Entities flushen(!!)
   * 
   * ansonsten schmeissen wir hier exceptions
   */
  public function __sleep() {
    $fields = array('identifier','entityName');
    
    if ($this->identifier > 0) {
      return $fields;
    }
    
    if (isset($this->entity) && $this->entity->getIdentifier() > 0) { // update
      $this->identifier = $this->entity->getIdentifier();
      return $fields;
    }
    
    if ($this->isPersisted()) {
      $this->em->flush();
      
      return $fields;
    } else {
      throw new \Psc\Exception('isset:'.(isset($this->entity) ? 1 : 0).' EntityReference kann nicht serialisiert werden, da der identifier nicht gesetzt ist und das Entity nicht persistent gemacht wurde. Auf dem Entity der Reference muss vorher persist() aufgerufen werden');
    }
  }
  
  public function isPersisted() {
    return (isset($this->entity) && $this->em->getUnitOfWork()->isScheduledForInsert($this->entity));
  }
  
  // Hier muss unbedingt EM gesetzt werden
  public function __wakeup() {
    $this->entityName = DoctrineHelper::getEntityName($this->entityName);
  }
  
  public static function construct(\Psc\Doctrine\Entity $entity, \Doctrine\ORM\EntityManager $em) {
    $ref = new static($entity->getEntityName(), $entity->getIdentifier(), $em);
    $ref->setEntity($entity);
    return $ref;
  }
  
  public function setEntityManager(\Doctrine\ORM\EntityManager $em) {
    $this->em = $em;
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function getIdentifier() {
    return $this->identifier;
  }
  
  public function __toString() {
    return sprintf('[DoctrineReference<%s> %s', $this->entityName, $this->identifier);
  }
}
?>