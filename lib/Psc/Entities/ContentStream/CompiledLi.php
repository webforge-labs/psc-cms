<?php

namespace Psc\Entities\ContentStream;

use Closure;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledLi extends Entry {
  
  /**
   * @var array
   * @ORM\Column(type="array")
   */
  protected $content;
  
  public function __construct(Array $content) {
    $this->setContent($content);
  }
  
  /**
   * @return array
   */
  public function getContent() {
    return $this->content;
  }
  
  /**
   * @param array $content
   */
  public function setContent(Array $content) {
    $this->content = $content;
    return $this;
  }
  
  public function serialize($context, Closure $serializeEntry) {
    return $this->doSerialize(array('content'), $serializeEntry, array(), $context);
  }
  
  public function getLabel() {
    return 'Aufzählung';
  }
  
  public function html() {
    $lis = array();
    
    foreach ($this->content as $li) {
      $lis[] = \Psc\HTML\HTML::tag('li', $this->wrapText($li, $inline=TRUE)->convert());
    }
    
    return \Psc\HTML\HTML::tag('ul', $lis, array('class'=>'roll'));
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\CompiledLi';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'content' => new \Psc\Data\Type\ArrayType(),
    ));
  }
}
?>