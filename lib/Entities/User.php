<?php

namespace Entities;

/**
 * @Entity(repositoryClass="Entities\UserRepository")
 * @Table(name="users")
 */
class User extends \Psc\CMS\User {
  
  public function getEntityName() {
    return 'Entities\User';
  }
}
?>