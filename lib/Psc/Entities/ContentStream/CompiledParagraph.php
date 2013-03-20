<?php

namespace Psc\Entities\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledParagraph extends Entry {
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  /**
   * @var string
   * @ORM\Column(type="text")
   */
  protected $content;
  
  public function __construct($content) {
    $this->setContent($content);
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
  public function getContent() {
    return $this->content;
  }
  
  /**
   * @param string $content
   */
  public function setContent($content) {
    $this->content = $content;
    return $this;
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\CompiledParagraph';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Psc\Data\Type\IdType(),
      'content' => new \Psc\Data\Type\MarkupTextType(),
    ));
  }
}
?>