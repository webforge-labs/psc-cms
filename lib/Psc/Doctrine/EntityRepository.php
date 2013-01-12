<?php

namespace Psc\Doctrine;

use Doctrine\ORM\NoResultException,
    Doctrine\ORM\QueryBuilder,
    \Psc\Code\Code,
    \Psc\Doctrine\Helper as DoctrineHelper,
    \Psc\Doctrine\Filter,
    \Psc\Doctrine\EntityFilter,
    
    \Psc\A;
use Closure;


/**
 * Erweitert ein Doctrine EntityRepository mit ganz nettem Kram
 *
namespace Entities;

use Psc\Doctrine\EntityRepository,
    Doctrine\ORM\NoResultException
;
 
class UserRepository extends EntityRepository {   
}


und dann
@Entity(repositoryClass="Entities\UserRepository")
im User-Entity machen
 */
class EntityRepository extends \Doctrine\ORM\EntityRepository {
  
  
  /**
   * Persisted das Entity und flushed den EntityManager
   * 
   */
  public function save(Entity $entity) {
    $this->_em->persist($entity);
    $this->_em->flush();
    return $this;
  }

  public function persist(Entity $entity) {
    $this->_em->persist($entity);
    return $this;
  }
  
  /**
   * Ruft auch remove() auf dem Entity auf, wenn die Funktion existiert
   *
   * @ducktyped
   * ruft keinen flush auf
   */
  public function remove(Entity $entity) {
    if (method_exists($entity,'remove')) {
      $entity->remove();
    }
    $this->_em->remove($entity);
    
    return $this;
  }

  /**
   * Ruft auch remove() auf dem Entity auf, wenn die Funktion existiert
   *
   * @ducktyped
   * ruft direkt flush auf
   */
  public function delete(Entity $entity) {
    $this->remove($entity);
    $this->_em->flush();
    
    return $this;
  }


  /**
   * Sowie find() aber mit Exception, erwartet nur ein Ergebnis
   * 
   * @throws \Psc\Doctrine\EntityNotFoundException
   * @TODO was passiert hier bei uneindeutigkeit?
   */
  public function hydrate($identifier) {
    $entity = $this->find($identifier);
    
    if (!($entity instanceof $this->_entityName)) {
      throw new EntityNotFoundException(
        sprintf("Entity: '%s' nicht gefunden: identifier: %s",
                $this->_entityName, Code::varInfo($identifier)));
    }
    
    return $entity;
  }
  
  
  /**
   * Prüft nach und nach in allen $criteria-Arrays in $criterias nach einem Vorkommen in diesem Repository
   *
   * wird keins gefunden, wird eine Doctrine\ORM\NoResultException geworden
   * @throws Doctrine\ORM\NoResultException
   */
  public function findOneByCriterias(Array $criterias) {
    foreach ($criterias as $criteria) {
      try {
        if (!is_array($criteria)) throw new \InvalidArgumentException('Elemente von $criterias müssen Arrays sein. '.Code::varInfo($criterias));
        $object = $this->findOneBy($criteria);
        
        if ($object != null) return $object;
      } catch (NoResultException $e) {
        
      }
    }
    
    throw new NoResultException();
  }
  
  /**
   * Sowie findOneBy aber mit exception
   *
   * @throws \Psc\Exception
   */
  public function hydrateBy(array $criterias) {
    $entity = $this->findOneBy($criterias);
    
    if (!($entity instanceof $this->_entityName)) {
      throw new EntityNotFoundException(sprintf('Entity %s nicht gefunden: criterias: %s', $this->_entityName, Code::varInfo($criterias)));
    }
    
    return $entity;
  }
  
  /**
   * @return array
   */
  public function findAllByIds(Array $ids, $idname = 'id') {
    if (count($ids) == 0) return array();
    
    $dql = 'SELECT e FROM '.$this->_entityName.' as e ';
    $dql .= 'WHERE e.'.$idname;
    $dql .= ' IN ('.DoctrineHelper::implodeIdentifiers($ids).') ';  // wenn ids ein verschalter array ist mit einem weiteren array mit strings drin, kommt hier ein notice
    $q = $this->_em->createQuery($dql);
    
    return $q->getResult();
  }
  
  /** 
   * @param $field das Feld auf welches der $term untersucht werden soll
   * ist Term nicht gesetzt werden alle Elemente zurückgegeben
   *
   * $filters können zusätzliche filter sein (für ableitende klassen)
   * achtung! Vorsicht beim Ableiten mit JOINS von Autocomplete term und maxresults
   */
  public function autoCompleteTerm(Array $fields, $term = NULL, Array $filters = array(), $maxResults = NULL) {
    $qb = $this->buildQueryAutoCompleteTerm($fields, $term);
    
    $q = $qb->getQuery();
    
    if ($maxResults) {
      $q->setMaxResults($maxResults);
    }
    
    return $q->getResult();
  }
  
  protected function buildQueryAutoCompleteTerm(Array $fields, $term) {
    $qb = new QueryBuilder($this->_em);
    
    $qb->select('e')
       ->from($this->_entityName, 'e');
    
    if (count($fields) > 0 && $term != "") {
      // term in quotes bedeutet wörtliche suche
      if (\Psc\Preg::match($term, '/^\s*("|\')(.*)("|\')\s*$/', $m)) {
        $term = $m[2];
        
        foreach ($fields as $field) {
          $qb->orWhere('e.'.$field.' = ?1 ');
        }
        
        $qb->setParameter(1,$term);
      } else {
        /* normale suche */
        foreach ($fields as $field) {
          $qb->orWhere('e.'.$field.' = ?1 ');
        
          for ($i = 2; $i <= 4; $i++) {
            $qb->orWhere('e.'.$field.' LIKE ?'.$i);
          }
        
        }
      
        $qb->setParameter(1,$term);
        $qb->setParameter(2,'%'.$term);
        $qb->setParameter(3,'%'.$term.'%');
        $qb->setParameter(4,$term.'%');
      }
    }
    
    return $qb;
  }
  
  
  /**
   * Setzt einen UniqueConstraintValidator mit den passenden UniqueConstraints des Entities
   *
   * sind keine Unique-Constraints für diese Klasse gesetzt, wird eine \Psc\Doctrine\NoUniqueConstraintException geworfen
   * @throws Psc\Doctrine\UniqueConstraintException, wenn $this->getUniqueIndex() einen inkonsistenten Index zurückgibt (index mit duplikaten)
   */
  public function configureUniqueConstraintValidator(UniqueConstraintValidator $validator = NULL) {
    $uniqueConstraints = $this->getUniqueConstraints();
    
    if (!isset($validator)) {
      // erstelle einen neuen
      $constraint = array_shift($uniqueConstraints);
      $validator = new UniqueConstraintValidator($constraint);
    }
    
    // benutze den bestehenden / füge die restlichen UniqueConstraints hinzu
    foreach ($uniqueConstraints as $constraint) {
      $validator->addUniqueConstraint($constraint);
    }
    
    /* verarbeiten die Daten für alle Constraints */
    foreach ($validator->getUniqueConstraints() as $constraint) {
      $validator->updateIndex($constraint, $this->getUniqueIndex($constraint));
    }
    
    return $validator;
  }
  
  /**
   * @return UniqueConstraint[]
   * @throws NoUniqueConstraintException
   */
  public function getUniqueConstraints() {
    $uniqueConstraints = array();
    $typeConverter = new TypeExporter(); // jaja ich weiß DPI wird ignoriert: weil wir später mal getMetaSet auf den neuen Enties für diese Reflection machen können und wir ihn dafür nicht mehr brauchen
    
    $metadata = $this->getClassMetadata();
    
    if (!isset($metadata->table['uniqueConstraints']) || count($uqs = $metadata->table['uniqueConstraints']) === 0) {
      throw new NoUniqueConstraintException('Die Klasse '.$this->getEntityName().' hat keine UniqueConstraints');
    }
    
    foreach ($uqs as $name => $definition) {
      $keys = array();
      foreach ($definition['columns'] as $column) {
        $field = $metadata->getFieldForColumn($column);
        if ($metadata->isAssociationWithSingleJoinColumn($field)) {
          $targetField = $metadata->getSingleAssociationReferencedJoinColumnName($field);
          $targetClassMetadata = $this->_em->getClassMetadata($metadata->getAssociationTargetClass($field));
          $dcColumnType = $targetClassMetadata->getTypeOfColumn('id');
        } else {
          $dcColumnType = $metadata->getTypeOfColumn($column);
        }
        try {
          $keys[$field] = $typeConverter->getPscType($dcColumnType);
        } catch (\Psc\Data\Type\TypeConversionException $e) {
          throw new \Psc\Exception('Der UniqueConstraint '.$name.' in Table '.$metadata->table['name'].' kann nicht zurückgegeben werden, da type Informationen für das Feld: '.$column.' fehlen. @Column hinzufügen.');
        }
      }
      
      $uniqueConstraints[$name] = new UniqueConstraint($name, $keys);
    }
    return $uniqueConstraints;
  }
  
  public function getUniqueIndex(UniqueConstraint $constraint, $qbFunction = NULL) {
    
    $qb = new QueryBuilder($this->_em);
    
    // alle Felder des Constraints müssen in den Index
    foreach ($constraint->getKeys() as $key) {
      $qb->addSelect(sprintf('e.%s', $key));
    }
    
    // der Identifier muss auch mit selected werden
    $identifierField = $this->getIdentifier(TRUE);
    // foreach as $identifierField) {
      $qb->addSelect(sprintf('e.%s AS %s',$identifierField,'identifier'));
    //}
    
    // alle Entities aus der Tabelle
    $qb->from($this->_entityName, 'e');
    
    return $this->deliverQuery($qb, $qbFunction, 'scalar'); // als array ausgeben für performance (wir wollen ja eh ein Array zurückgeben)
  }
  
  /**
   * Ruft den qbFunction-Hook auf dem QueryBuilder auf und gibt entsprechend ein Result oder den QueryBuilder zurück
   *
   * der Hook kann unterbrechen, dass das Query direkt ausgeführt wird (return) oder bestimmen in welcher Form das Result zurückgegeben werden soll
   * 
   * die qbFunction kann folgende Strings zurückgeben umn das Verhalten von deliverQuery zu steuern:
   *   - return: gibt den QueryBuilder zurück
   *   - result  erstellt das Query und gibt das Ergebnis von query::getResult() zurück
   *   - single  erstellt das Query und gibt das Ergebnis von query::getSingleResult() zurück
   *   - scalar  erstellt das Query und gibt das Ergebnis von query::getScalarResult() zurück
   *
   * die qbFunction erhält nur einen Parameter dies ist der QueryBuilder
   * @param string $returnDefault ist qbFunction nicht gesetzt, wird dies als rückgabe Wert genommen (selbes format wie strings oben)
   * @return mixed
   */
  public function deliverQuery($queryBuilder, $qbFunction, $returnDefault) {
    $qbFunction = $this->compileQBFunction($qbFunction);
    
    $return = $qbFunction($queryBuilder) ?: $returnDefault;
    
    if ($queryBuilder instanceof \Doctrine\ORM\QueryBuilder) {
      $query = $queryBuilder->getQuery();
    } elseif ($queryBuilder instanceof \Doctrine\ORM\AbstractQuery) {
      $query = $queryBuilder;
    } else {
      throw new \Psc\Exception('Parameter 1 kann nur QueryBuilder oder Query sein.');
    }
    
    try {
      if ($return === 'return') {
        return $queryBuilder;
      } elseif ($return === 'result') {
        return $query->getResult();
      } elseif ($return === 'single') {
        return $query->getSingleResult();
      } elseif ($return === 'scalar') {
        return $query->getScalarResult();
      } elseif (mb_strtolower($return) === 'singleornull') {
        
        try {
          return $query->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
          return NULL;
        }
        
      } else {
        return $query->getResult();
      }
    } catch (\Doctrine\ORM\Query\QueryException $e) {
      throw new \Psc\Doctrine\QueryException(sprintf("%s \n\nDQL: '%s'",$e->getMessage(), $query->getDQL()));
    
    } catch (\Doctrine\ORM\NoResultException $e) {
      throw \Psc\Doctrine\EntityNotFoundException::fromQuery($query);
      
    } catch (\Doctrine\ORM\NonUniqueResultException $e) {
      throw \Psc\Doctrine\EntityNonUniqueResultException::fromQuery($query);
    }
  }
  
  protected function compileQBFunction($qbFunction) {
    if (!isset($qbFunction)) {
      return function ($qb) {
        return NULL; // wir lassen als default deliverQuery entscheiden, was der Default sein soll
      };
    }
    
    if (!($qbFunction instanceof Closure)) {
      throw new \InvalidArgumentException('qbFunction muss ein Closure sein mit erstem Parameter QueryBuilder');
    }
    
    return $qbFunction;
  }

  public function getIdentifier($single = TRUE) {
    // der Identifier muss auch mit selected werden
    $identifiers = $this->getClassMetadata()->getIdentifierFieldNames();
    
    if ($identifiers == NULL) {
      throw new \Psc\Exception('Das Entity '.$this->getEntityName().' hat keinen Identifier! (ClassMetadata gibt keinen zurück)');
    }
    
    if ($single) {
      if (count($identifiers) > 1) {
        throw new \Psc\Code\NotImplementedException('Composite Identifiers werden nicht unterstützt: '.Code::varInfo($identifiers));
      }
      return array_pop($identifiers);
    }
    
    return $identifiers;
  }

  /**
   *
   *
   * deprecated: lieber deliverQuery benutzen
   */
  protected function processQuery(\Doctrine\ORM\AbstractQuery $q, $mode = 'SingleResult', $ident = NULL) {
    if (\Psc\PSC::inProduction()) {
      throw new \Psc\DeprecatedException(__FUNCTION__.' ist deprecated');
    }
    
    if ($mode == 'SingleResult') {
      try {
        return $q->getSingleResult();
        
      } catch (\Doctrine\ORM\NoResultException $e) {
        $e = new \Psc\Doctrine\EntityNotFoundException('Es konnte kein Ergebnis für den Identifier: '.Code::varInfo($ident).' gefunden werden.');
        $e->findCriteria = $q->getParameters();
        
        throw $e;

      } catch (\Doctrine\ORM\NonUniqueResultException $e) {
        $e = new \Psc\Doctrine\EntityNonUniqueResultException('Es wurde mehr als ein Ergebnis für den Identifier: '.Code::varInfo($ident).' gefunden. ');
        $e->findCriteria = $q->getParameters();
        
        throw $e;
      }
    
    }
    
    throw new \Psc\Exception('Falscher parameter für mode: '.Code::varInfo($mode));
  }
  
  public function getClassMetadata() {
    return parent::getClassMetadata();
  }
  
  public function getEntityManager() {
    return parent::getEntityManager();
  }
}
?>