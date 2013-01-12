<?php

namespace Psc\Doctrine\TestEntities;

use Psc\DateTime\DateTime;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * FormPanel Test erstellt dieses objekt (also eher compiled person)
 * @ORM\Entity(repositoryClass="Psc\Doctrine\TestEntities\PersonRepository")
 * @ORM\Table(name="test_persons")
 */
class Person extends CompiledPerson {
  
  public function setIdentifier($id) {
    $this->id = $id;
    return $this;
  }

  public function getEntityName() {
    return 'Psc\Doctrine\TestEntities\Person';
  }
}
?>