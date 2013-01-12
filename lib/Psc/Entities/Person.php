<?php

namespace Psc\Entities;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="persons")
 */
class Person extends \Psc\Doctrine\Entities\BasicPerson {

  public function getEntityName() {
    return 'Entities\Person';
  }
}
?>