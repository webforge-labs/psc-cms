<?php

namespace Psc\Doctrine\Entities;

use Doctrine\ORM\NoResultException,
    \Psc\Doctrine\Helper as DoctrineHelper
;

class BasicPersonRepository extends \Psc\Doctrine\EntityRepository {
  
  public function findAllIndexByEmail() {
    return DoctrineHelper::reindex($this->findAll(),'getEmail');
  }
}

?>