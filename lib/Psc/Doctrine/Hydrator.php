<?php

namespace Psc\Doctrine;

use Psc\Code\Code;
use Doctrine\ORM\EntityManager;

/*
 * Eine Einfache Klasse die aus IDs Objekte aus der Datenbank lädt
 *
 * dies kann sehr praktisch sein für die Bearbeitung von Formularen
 */
class Hydrator {
  
  /**
   * @var Doctrine\ORM\EntityManager
   */
  protected $em;
  
  protected $entityName;
  
  public function __construct($entityName, DCPackage $dc) {
    $this->em = $dc->getEntityManager();
    $this->entityName = $dc->getModule()->getEntityName($entityName);
  }
  
  /**
   * Hydratet einen String von Identifiern getrennt mit $sep
   *
   * @param string $idname wird im dql verwendet (also klein)
   */
  public function byIdentifierList($list, $sep = ',', $idname = 'id', $checkCardinality = TRUE, &$parsed = NULL, &$found = NULL) {
    if ($list == NULL) {
      return array();
    }
    
    if (is_array($list)) {
      $identifiers = $list;
    } else {
      $identifiers = explode($sep, trim($list));
    }
    $identifiers = array_map('trim',$identifiers);
    $identifiers = array_filter($identifiers, function ($identifier) {
      return !empty($identifier); // schließt auch 0 aus
    });
    
    $parsed = count($identifiers);
    if (count($identifiers) == 0) return array();
    $result = $this->getRepository()->findAllByIds($identifiers, $idname);
    
    $found = count($result);
    
    if ($checkCardinality && ($parsed != $found)) {
      throw new \Psc\Exception('Es konnten nicht alle identifier aus der Liste hydriert werden! Gefunden aus '.$list.': '.Code::varInfo($identifiers).' in der DB gefunden #'.count($result));
    }
    
    return $result;
  }
  
  /**
   *
   * $hydrator = new Hydrator('FoodTag', $doctrinePackage);
   * $tags = $hydrator->byList(array('hot','special','spicy'), 'label');
   * 
   * @return array
   */
  public function byList(Array $list, $field, $checkCardinality = TRUE, &$found = NULL) {
    if ($list == NULL)
      return array();
    
    $result = $this->getRepository()->findBy(array($field => $list));
    $found = count($result);
    
    if ($checkCardinality && $found != count($list)) {
      throw new \Psc\Exception('Es konnten nicht alle Felder aus der Liste geladen werden. Gefunden: '.$found.' in der Liste waren aber: '.count($list).' Liste: '.Code::varInfo($list));
    }
    
    return $result;
  }

  /**
   * @return Doctrine\Common\Collections\Collection
   */
  public function collection(Array $identifiers, $reindex = NULL, $checkCardinality = TRUE, &$found = NULL) {
    $idname = $reindex ?: 'id';
    $entities = $this->byList($identifiers, $idname, $checkCardinality, $found);
    
    if ($reindex !== NULL) {
      $entities = Helper::reindex($entities, $reindex);
    }
    
    return $entities;
  }

  
  public function getRepository() {
    return $this->em->getRepository($this->entityName);
  }
  
  public function setEntityManager(\Doctrine\ORM\EntityManager $em) {
    $this->em = $em;
  }
  
  public function getEntityManager() {
    return $this->em;
  }
}

?>