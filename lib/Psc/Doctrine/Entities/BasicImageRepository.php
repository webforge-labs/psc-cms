<?php

namespace Psc\Doctrine\Entities;

use Doctrine\ORM\NoResultException,
    \Psc\Doctrine\Helper as DoctrineHelper
;

class BasicImageRepository extends \Psc\Doctrine\EntityRepository {

  public function getInstance($sourcePath) {
    try {
      return $this->_em->hydrateBy(array('sourcePath'=>$sourcePath));
    } catch (\Psc\Doctrine\EntityNotFoundException $e) {
      $c = $this->getEntityName();
      $image = new $c($sourcePath);
    }
  }
  
  /**
   * Gibt alle Bilder nach hash indiziert zurück
   * 
   * @return array
   */
  public function findAllIndexByHash() {
    return DoctrineHelper::reindex($this->findAll(),'getHash');
  }
}
?>