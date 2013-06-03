<?php

namespace Psc\Doctrine\TestEntities;

use Webforge\Common\DateTime\Date;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledPerson extends \Psc\CMS\AbstractEntity {
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $name;
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $firstName;
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $email;
  
  /**
   * @var Webforge\Common\DateTime\Date
   * @ORM\Column(type="PscDate")
   */
  protected $birthday;
  
  /**
   * @var bool
   * @ORM\Column(type="boolean")
   */
  protected $yearKnown;
  
  public function __construct($name, $email = NULL, $firstName = NULL, Date $birthday = NULL) {
    $this->setName($name);
    if (isset($email)) {
      $this->setEmail($email);
    }
    if (isset($firstName)) {
      $this->setFirstName($firstName);
    }
    if (isset($birthday)) {
      $this->setBirthday($birthday);
    }
  }
  
  /**
   * @return integer
   */
  public function getId() {
    return $this->id;
  }
  
  /**
   * Gibt den Primärschlüssel des Entities zurück
   * 
   * @return mixed meistens jedoch einen int > 0 der eine fortlaufende id ist
   */
  public function getIdentifier() {
    return $this->id;
  }
  
  /**
   * @param mixed $identifier
   * @chainable
   */
  public function setIdentifier($id) {
    $this->id = $id;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * @param string $name
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getFirstName() {
    return $this->firstName;
  }
  
  /**
   * @param string $firstName
   */
  public function setFirstName($firstName) {
    $this->firstName = $firstName;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getEmail() {
    return $this->email;
  }
  
  /**
   * @param string $email
   */
  public function setEmail($email) {
    $this->email = $email;
    return $this;
  }
  
  /**
   * @return Webforge\Common\DateTime\Date
   */
  public function getBirthday() {
    return $this->birthday;
  }
  
  /**
   * @param Webforge\Common\DateTime\Date $birthday
   */
  public function setBirthday(Date $birthday) {
    $this->birthday = $birthday;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function getYearKnown() {
    return $this->yearKnown;
  }
  
  /**
   * @param bool $yearKnown
   */
  public function setYearKnown($yearKnown) {
    $this->yearKnown = $yearKnown;
    return $this;
  }
  
  public function getEntityName() {
    return 'Psc\Doctrine\TestEntities\CompiledPerson';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Psc\Data\Type\IdType(),
      'name' => new \Psc\Data\Type\StringType(),
      'firstName' => new \Psc\Data\Type\StringType(),
      'email' => new \Psc\Data\Type\EmailType(),
      'birthday' => new \Psc\Data\Type\BirthdayType(new \Psc\Code\Generate\GClass('Webforge\\Common\\DateTime\\Date')),
      'yearKnown' => new \Psc\Data\Type\BooleanType(),
    ));
  }
}
?>