<?php

namespace Psc\Entities\ContentStream;

use Closure;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledHeadline extends Entry {
  
  /**
   * @var string
   * @ORM\Column(type="text")
   */
  protected $content;
  
  /**
   * @var integer
   * @ORM\Column(type="smallint")
   */
  protected $level;
  
  public function __construct($content, $level = 1) {
    $this->setContent($content);
    if (isset($level)) {
      $this->setLevel($level);
    }
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
  
  /**
   * @return integer
   */
  public function getLevel() {
    return $this->level;
  }
  
  /**
   * @param integer $level
   */
  public function setLevel($level) {
    $this->level = $level;
    return $this;
  }
  
  public function serialize($context, Closure $serializeEntry) {
    return $this->doSerialize(array('content','level'), $serializeEntry, array(), $context);
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\CompiledHeadline';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'content' => new \Webforge\Types\MarkupTextType(),
      'level' => new \Webforge\Types\PositiveSmallIntegerType(),
    ));
  }
}
?>