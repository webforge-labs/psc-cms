<?php

namespace Psc\Entities\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledEntry extends \Psc\TPL\ContentStream\EntryEntity {
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  /**
   * @var integer
   * @ORM\Column(type="integer")
   */
  protected $sort = 1;
  
  /**
   * @var Psc\Entities\ContentStream\ContentStream
   * @ORM\ManyToOne(targetEntity="Psc\Entities\ContentStream\ContentStream", inversedBy="entries")
   * @ORM\JoinColumn(onDelete="cascade")
   */
  protected $contentStream;
  
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
   * @return integer
   */
  public function getSort() {
    return $this->sort;
  }
  
  /**
   * @param integer $sort
   */
  public function setSort($sort) {
    $this->sort = $sort;
    return $this;
  }
  
  /**
   * @return Psc\Entities\ContentStream\ContentStream
   */
  public function getContentStream() {
    return $this->contentStream;
  }
  
  /**
   * @param Psc\Entities\ContentStream\ContentStream $contentStream
   */
  public function setContentStream(ContentStream $contentStream = NULL) {
    $this->contentStream = $contentStream;
    if (isset($contentStream)) $contentStream->addEntry($this);

    return $this;
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\CompiledEntry';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Webforge\Types\IdType(),
      'sort' => new \Webforge\Types\PositiveIntegerType(),
      'contentStream' => new \Webforge\Types\EntityType(new \Psc\Code\Generate\GClass('Psc\\Entities\\ContentStream\\ContentStream')),
    ));
  }
}
?>