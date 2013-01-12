<?php

namespace Psc\Doctrine;

use Doctrine\ORM\EntityManager;

class ORMPurger extends \Doctrine\Common\DataFixtures\Purger\ORMPurger {
  
  private $em;
  
  public function __construct(EntityManager $em = null) {
    // jesus immer diese scheisse mit dem private
    $this->em = $em;
    parent::__construct($em);
  }
  
  public function purge() {
    if ($this->getPurgeMode() === self::PURGE_MODE_TRUNCATE) {
      // QND Hack: wenn mir jemand erklärt wies schön geht, gerne
      $this->em->getConnection()->executeQuery('set foreign_key_checks = 0');
      parent::purge();
      $this->em->getConnection()->executeQuery('set foreign_key_checks = 1');

    } else {
      parent::purge();
    }
  }
}
?>