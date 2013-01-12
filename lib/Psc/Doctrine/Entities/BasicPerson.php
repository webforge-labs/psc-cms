<?php

namespace Psc\Doctrine\Entities;

use Doctrine\ORM\Mapping as ORM;

abstract class BasicPerson extends \Psc\Doctrine\Object {

  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @var int
   */
  protected $id;
  
  /**
   * @ORM\Column(type="string",nullable=true)
   * @var string
   */
  protected $email;
  
  /**
   * Der volle (Anzeige-Name)
   *
   * @ORM\Column(type="string")
   * @var string
   */
  protected $displayName;
  
  /**
   * @ORM\Column(type="string",nullable=true)
   * @var string
   */
  protected $telephone;
  
  /**
   * @ORM\Column(type="string",nullable=true)
   * @var string
   */
  protected $cell;

  /**
   * @ORM\Column(type="string",nullable=true)
   * @var string
   */
  protected $fax;
  
  public function getIdentifier() {
    return $this->id;
  }
  
  public function __toString() {
    return '['.$this->id.'] '.$this->displayName;
  }
}

?>