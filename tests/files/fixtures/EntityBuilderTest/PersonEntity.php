<?php

namespace Psc\Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\PersonRepository")
 * @ORM\Table(name="persons")
 */
class Person extends CompiledPerson {
  
  public function getContextLabel($context = 'default') {
    if ($context === self::CONTEXT_DEFAULT) {
      return parent::getContextLabel();
    }
    
    return parent::getContextLabel();
  }
  
  public function getEntityName() {
    return 'Psc\Entities\Person';
  }
}
?>