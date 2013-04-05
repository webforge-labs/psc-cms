<?php

namespace Psc\Entities\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledParagraph extends Entry {
  
  /**
   * @var string
   * @ORM\Column(type="text")
   */
  protected $content;
  
  public function __construct($content) {
    $this->setContent($content);
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
      'content' => new \Psc\Data\Type\MarkupTextType(),
    ));
  }
}
?>