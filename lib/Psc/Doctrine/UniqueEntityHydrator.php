<?php

namespace Psc\Doctrine;

/**
 * Hydriert ein Entity anhand seiner Primären funktionalen Abhängigkeiten
 *
 * an getEntity() müssen ALLE Felder des Entities übergeben werden die entweder:
 *  - Teil eines Unique-Schlüssels sind
 *  - Teil des Primär-Schlüssels sind
 *
 * will man diese nicht berücksichtigen kann man diese keys als NULL übergeben (aufpassen, falls man NULL-Werte im Unique-Key hat)
 */
class UniqueEntityHydrator extends \Psc\SimpleObject {
  
  /**
   * @var \Psc\Doctrine\EntityRepository
   */
  protected $repository;
  
  /**
   * Cache für das Query für die UniqueHydration
   *
   */
  protected $queryBuilder;
  
  /**
   *
   * Index über die benutzten Fields
   * @var array
   */
  protected $queryFields = array();
  
  public function __construct(EntityRepository $repository) {
    $this->repository = $repository;
  }
  
  /**
   * Hydriert das Entity aus einem Array mit Daten für Primary Keys oder Unique Constraints
   *
   * es wird NULL zurückgegeben wenn kein UniqueConstraint und kein Primary Key matched
   * @return Psc\Doctrine\Entity|NULL
   *
   * @TODO Psc\Doctrine\QueryException fangen und erklären was fehlt / was geegeben ist
   */
  public function getEntity(Array $fieldsData) {
    $qb = $this->getQueryBuilder();
    
    $qFields = array_keys($this->queryFields);
    // short track querying if standard case: only id
    if ($qFields === array('id')) {
      // this saves a query, because the qb->getQuery is executed everytime, ever ever
      return isset($fieldsData['id']) ? $this->repository->find($fieldsData['id']) : NULL;
    }
    
    // sicherheit: parameter filtern ist zwar nicht nötig, weil wir ja sowieso nur feste parameter im statement haben
    // aber doctrine ist picky, wenn man auch zu viele felder übergibt
    $binds = array();
    foreach ($fieldsData as $field => $value) {
      if (array_key_exists($field, $this->queryFields)) {
        if ($this->repository->getClassMetadata()->isSingleValuedAssociation($field)) {
          $value = $value->getIdentifier();
        }
        $qb->setParameter($field, $value);
        $binds[$field] = TRUE;
      }
    }
    
    if (count($qFields) !== count($bindFields = array_keys($binds))) {
      throw new \RuntimeException(
        'Die Anzahl er Parameter im Query und die Anzahl der gebundenen Werte ist nicht gleich. Dies gibt eine QueryException:'."\n".
        $qb->getDQL()."\n".
        Helper::debugCollectionDiff($qFields, $bindFields, 'query Fields', 'parameter/bound Fields')
      );
    }
    
    $result = $this->repository->deliverQuery($qb, NULL, 'singleOrNull');
    return $result;
  }
  
  /**
   * @return Doctrine\ORM\QueryBuilder
   */
  protected function getQueryBuilder() {
    if (!isset($this->queryBuilder)) {
      $qb = $this->repository->createQueryBuilder('entity');
  
      /* Überblick:
         wir bauen hier sowas wie das hier:
         
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('tag.label', ':label'),
                $qb->expr()->eq('tag.id', ':id')
              ));
      
        dies wäre für den Fall das id der Primary Key von Tag ist und label ein UniqueConstraint für Tag ist
      */
  
      // wir stellen das where (für unten) zusammen
      $hydrateConditions = $qb->expr()->orX();
      
      try {
        // über den UniqueConstraint kann das Entity über eine AND() verknüpfung der keys des Constraints gefunden werden
        foreach ($this->repository->getUniqueConstraints() as $name => $uniqueConstraint) {
          // hier könnten wir die UQs filtern (yagni)
        
          $constraintCondition = $qb->expr()->andX();
          foreach ($uniqueConstraint->getKeys() as $key) {
            $constraintCondition->add($qb->expr()->eq(sprintf('entity.%s', $key), ':'.$key));
            $this->queryFields[$key] = TRUE;
          }
          $hydrateConditions->add($constraintCondition);
        }
      } catch (NoUniqueConstraintException $e) {
        // ok
      }
      
      // der Primäry key ist durch :id findbar
      $hydrateConditions->add($qb->expr()->eq(sprintf('entity.%s', $pk = $this->getPrimaryKey()), ':id'));
      $this->queryFields[$pk] = TRUE;
      
      $qb->where($hydrateConditions);
      
      $this->queryBuilder = $qb;
    }
    
    return $this->queryBuilder;
  }
  
  protected function getPrimaryKey() {
    return $this->repository->getIdentifier(TRUE);
  }
  

  /**
   * @return Psc\Doctrine\EntityRepository
   */
  public function getRepository() {
    return $this->repository;
  }
}
?>