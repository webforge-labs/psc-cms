<?php

namespace Psc\Entities\ContentStream;

use Doctrine\Common\Collections\Collection;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledContentStream extends \Psc\TPL\ContentStream\ContentStreamEntity {
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  /**
   * @var string
   * @ORM\Column(nullable=true)
   */
  protected $locale;
  
  /**
   * @var string
   * @ORM\Column(nullable=true)
   */
  protected $slug;
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $type = 'page-content';
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $revision = 'default';
  
  /**
   * @var Doctrine\Common\Collections\Collection<Psc\Entities\ContentStream\Entry>
   * @ORM\OneToMany(mappedBy="contentStream", targetEntity="Psc\Entities\ContentStream\Entry", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
   * @ORM\OrderBy({"sort"="ASC"})
   */
  protected $entries;
  
  public function __construct($locale = NULL, $slug = NULL, $revision = 'default') {
    if (isset($locale)) {
      $this->setLocale($locale);
    }
    if (isset($slug)) {
      $this->setSlug($slug);
    }
    if (isset($revision)) {
      $this->setRevision($revision);
    }
    $this->entries = new \Psc\Data\ArrayCollection();
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
   * @return string
   */
  public function getLocale() {
    return $this->locale;
  }
  
  /**
   * @param string $locale
   */
  public function setLocale($locale) {
    $this->locale = $locale;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getSlug() {
    return $this->slug;
  }
  
  /**
   * @param string $slug
   */
  public function setSlug($slug) {
    $this->slug = $slug;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getType() {
    return $this->type;
  }
  
  /**
   * @param string $type
   */
  public function setType($type) {
    $this->type = $type;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getRevision() {
    return $this->revision;
  }
  
  /**
   * @param string $revision
   */
  public function setRevision($revision) {
    $this->revision = $revision;
    return $this;
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Psc\Entities\ContentStream\Entry>
   */
  public function getEntries() {
    return $this->entries;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<CoMun\Entities\ContentStream\Entry> $entries
   */
  public function setEntries(Collection $entries) {
    $this->entries = $entries;
    return $this;
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\CompiledContentStream';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Psc\Data\Type\IdType(),
      'locale' => new \Psc\Data\Type\StringType(),
      'slug' => new \Psc\Data\Type\StringType(),
      'type' => new \Psc\Data\Type\StringType(),
      'revision' => new \Psc\Data\Type\StringType(),
      'entries' => new \Psc\Data\Type\PersistentCollectionType(new \Psc\Code\Generate\GClass('Psc\\Entities\\ContentStream\\Entry')),
    ));
  }
}
?>