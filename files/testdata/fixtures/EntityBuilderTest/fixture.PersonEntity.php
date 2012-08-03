<?php

namespace Psc\Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Gedmo\Mapping\Annotation AS Gedmo;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\PersonRepository")
 * @ORM\Table(name="persons")
 */
abstract class Person extends CompiledPerson {
  
  public function getEntityName() {
    return 'Psc\Entities\Person';
  }
}
?>