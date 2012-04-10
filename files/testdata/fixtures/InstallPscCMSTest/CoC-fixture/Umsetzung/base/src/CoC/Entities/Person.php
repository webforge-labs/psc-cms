<?php

namespace CoC\Entities;

use Psc\Data\ArrayCollection;

/**
 * @Entity(repositoryClass="CoC\Entities\PersonRepository")
 * @Table(name="persons")
 */
class Person extends \Psc\Doctrine\Object {

  /**
   * @var integer
   * @Id
   * @GeneratedValue
   * @Column(type="integer")
   */
  protected $id;

  public function getEntityName() {
    return 'CoC\Entities\Person';
  }

  public function getId() {
    return $this->id;
  }

  public function getIdentifier() {
    return $this->id;
  }
}
?>