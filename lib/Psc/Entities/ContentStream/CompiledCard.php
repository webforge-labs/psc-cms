<?php

namespace Psc\Entities\ContentStream;

use Closure;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledCard extends Entry {
  
  /**
   * @var Psc\Entities\ContentStream\ContentStreamWrapper
   * @ORM\OneToOne(targetEntity="Psc\Entities\ContentStream\ContentStreamWrapper")
   * @ORM\JoinColumn(nullable=false, onDelete="SET NULL")
   */
  protected $content;
  
  public function __construct(ContentStreamWrapper $content) {
    $this->setContent($content);
  }
  
  /**
   * @return Psc\Entities\ContentStream\ContentStreamWrapper
   */
  public function getContent() {
    return $this->content;
  }
  
  /**
   * @param Psc\Entities\ContentStream\ContentStreamWrapper $content
   */
  public function setContent(ContentStreamWrapper $content) {
    $this->content = $content;
    return $this;
  }
  
  public function serialize($context, Closure $serializeEntry) {
    return $this->doSerialize(array('content'), $serializeEntry, array('specification'=>(object) array('name'=>'Card','section'=>'misc','label'=>'Standort Visitenkarte','contexts'=>array('0'=>'page-content'),'fields'=>(object) array('content'=>(object) array('type'=>'content-stream','label'=>'Inhalt')))), $context);
  }
  
  public function getLabel() {
    return 'Standort Visitenkarte';
  }
  
  public function html() {
    return 'Card';
  }
  
  public function getType() {
    return 'TemplateWidget';
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\CompiledCard';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'content' => new \Psc\Data\Type\EntityType(new \Psc\Code\Generate\GClass('Psc\\Entities\\ContentStream\\ContentStreamWrapper')),
    ));
  }
}
?>