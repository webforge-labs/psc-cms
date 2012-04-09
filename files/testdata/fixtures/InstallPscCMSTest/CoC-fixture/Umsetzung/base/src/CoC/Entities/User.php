<?php

namespace CoC\Entities;

use Psc\Data\ArrayCollection;

/**
 * @Entity(repositoryClass="CoC\Entities\UserRepository")
 * @Table(name="users")
 */
class User extends \Psc\CMS\User {

  public function getEntityName() {
    return 'CoC\Entities\User';
  }
}
?>