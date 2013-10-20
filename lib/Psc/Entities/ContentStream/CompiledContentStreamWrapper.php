<?php

namespace Psc\Entities\ContentStream;

use Closure;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledContentStreamWrapper extends Entry {
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  /**
   * @var Psc\Entities\ContentStream\ContentStream
   * @ORM\OneToOne(targetEntity="Psc\Entities\ContentStream\ContentStream", cascade={"persist", "remove"})
   * @ORM\JoinColumn(nullable=false, onDelete="cascade")
   */
  protected $wrapped;
  
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
   * @return Psc\Entities\ContentStream\ContentStream
   */
  public function getWrapped() {
    return $this->wrapped;
  }
  
  /**
   * @param Psc\Entities\ContentStream\ContentStream $wrapped
   */
  public function setWrapped(ContentStream $wrapped) {
    $this->wrapped = $wrapped;
    return $this;
  }
  
  public function serialize($context, Closure $serializeEntry) {
    return $this->doSerialize(array('wrapped'), $serializeEntry, array(), $context);
  }
  
  public function getLabel() {
    return 'ContentStreamWrapper';
  }
  
  public function html() {
    return '';
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\CompiledContentStreamWrapper';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Webforge\Types\IdType(),
      'wrapped' => new \Webforge\Types\EntityType(new \Psc\Code\Generate\GClass('Psc\\Entities\\ContentStream\\ContentStream')),
    ));
  }
}
?>