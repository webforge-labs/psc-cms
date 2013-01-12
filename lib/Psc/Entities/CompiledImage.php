<?php

namespace Psc\Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledImage extends \Psc\Doctrine\Entities\BasicImage2 {
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $sourcePath;
  
  /**
   * @var string
   * @ORM\Column(unique=true)
   */
  protected $hash;
  
  /**
   * @var string
   * @ORM\Column(nullable=true)
   */
  protected $label;
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  public function __construct($sourcePath = NULL, $label = NULL, $hash = NULL) {
    if (isset($sourcePath)) {
      $this->setSourcePath($sourcePath);
    }
    if (isset($label)) {
      $this->setLabel($label);
    }
    if (isset($hash)) {
      $this->setHash($hash);
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
   * @return string relativ zum Datenverzeichnis des Managers (nicht full path!)
   */
  public function getSourcePath() {
    return $this->sourcePath;
  }
  
  /**
   * @param string $sourcePath
   */
  public function setSourcePath($sourcePath) {
    $this->sourcePath = $sourcePath;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getHash() {
    return $this->hash;
  }
  
  /**
   * @param string $hash
   */
  public function setHash($hash) {
    $this->hash = $hash;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }
  
  /**
   * @param string $label
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }
  
  /**
   * @ORM\PostRemove
   */
  public function triggerRemoved() {
    return parent::triggerRemoved();
  }
  
  public function getEntityName() {
    return 'Psc\Entities\CompiledImage';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Psc\Data\Type\IdType(),
      'sourcePath' => new \Psc\Data\Type\StringType(),
      'hash' => new \Psc\Data\Type\StringType(),
      'label' => new \Psc\Data\Type\StringType(),
    ));
  }
}
?>