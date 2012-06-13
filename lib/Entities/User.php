<?php

namespace Entities;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Entities\UserRepository")
 * @ORM\Table(name="users")
 */
class User extends \Psc\CMS\User {
  
  public function getEntityName() {
    return 'Entities\User';
  }
}
?>