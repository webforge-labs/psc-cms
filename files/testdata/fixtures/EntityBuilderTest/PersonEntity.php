<?php

namespace Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Entities\PersonRepository")
 * @ORM\Table(name="persons")
 */
class Person extends CompiledPerson {
  
  public function getEntityName() {
    return 'Entities\Person';
  }
}
?>