<?php

namespace Psc\Entities\ContentStream;

use Closure;
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
  
  public function serialize($context, Closure $serializeEntry) {
    return $this->doSerialize(array('content'), $serializeEntry, array(), $context);
  }
  
  public function getLabel() {
    return 'Absatz';
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\CompiledParagraph';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'content' => new \Webforge\Types\MarkupTextType(),
    ));
  }
}
?>