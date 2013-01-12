<?php

namespace Psc\Entities;

use Webforge\Common\System\File;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledFile extends \Psc\Doctrine\Entities\BasicUploadedFile {
  
  /**
   * @var Webforge\Common\System\File
   */
  protected $file;
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  /**
   * @var string
   * @ORM\Column(unique=true)
   */
  protected $hash;
  
  /**
   * @var string
   * @ORM\Column(nullable=true)
   */
  protected $description;
  
  /**
   * @var string
   * @ORM\Column(nullable=true)
   */
  protected $originalName;
  
  public function __construct(File $file, $description = NULL) {
    $this->setFile($file);
    if (isset($description)) {
      $this->setDescription($description);
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
   * Gibt den Hash zurück der den Inhalt der Datei eindeutig hashed
   * 
   * die Standardimplementierung sollte sha1 sein ist aber nicht relevant
   * @return string
   */
  public function getHash() {
    return $this->hash;
  }
  
  /**
   * @return string
   */
  public function setHash($hash) {
    $this->hash = $hash;
    return $this;
  }
  
  /**
   * Gibt die Kurzbeschreibung der Datei zurück
   * 
   * die Kurzbeschreibung kann z.B. als Linkbeschriftung der File benutzt werden und ist eine etwas schönere Darstellung des Datei-Namens
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }
  
  /**
   * Setzt die Kurzbeschreibung der Datei
   * 
   * @param string $description
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getOriginalName() {
    return $this->originalName;
  }
  
  /**
   * @param string $originalName
   */
  public function setOriginalName($originalName) {
    $this->originalName = $originalName;
    return $this;
  }
  
  public function getEntityName() {
    return 'Psc\Entities\CompiledFile';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Psc\Data\Type\IdType(),
      'hash' => new \Psc\Data\Type\StringType(),
      'description' => new \Psc\Data\Type\StringType(),
      'originalName' => new \Psc\Data\Type\StringType(),
    ));
  }
}
?>