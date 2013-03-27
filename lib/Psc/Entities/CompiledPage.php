<?php

namespace Psc\Entities;

use Psc\DateTime\DateTime;
use Doctrine\Common\Collections\Collection;
use Psc\Entities\ContentStream\ContentStream;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledPage extends \Psc\CMS\Roles\PageEntity {
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $slug;
  
  /**
   * @var bool
   * @ORM\Column(type="boolean")
   */
  protected $active = true;
  
  /**
   * @var Psc\DateTime\DateTime
   * @ORM\Column(type="PscDateTime")
   */
  protected $created;
  
  /**
   * @var Psc\DateTime\DateTime
   * @ORM\Column(type="PscDateTime", nullable=true)
   */
  protected $modified;
  
  /**
   * @var Doctrine\Common\Collections\Collection<Psc\Entities\NavigationNode>
   * @ORM\OneToMany(mappedBy="page", targetEntity="Psc\Entities\NavigationNode")
   */
  protected $navigationNodes;
  
  /**
   * @var Doctrine\Common\Collections\Collection<Psc\Entities\ContentStream\ContentStream>
   * @ORM\ManyToMany(targetEntity="Psc\Entities\ContentStream\ContentStream")
   * @ORM\JoinTable(name="page2contentstream", joinColumns={@ORM\JoinColumn(name="page_id", onDelete="cascade")}, inverseJoinColumns={@ORM\JoinColumn(name="contentstream_id", onDelete="cascade")})
   */
  protected $contentStreams;
  
  public function __construct($slug) {
    $this->setSlug($slug);
    $this->navigationNodes = new \Psc\Data\ArrayCollection();
    $this->contentStreams = new \Psc\Data\ArrayCollection();
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
   * @return bool
   */
  public function getActive() {
    return $this->active;
  }
  
  public function setActive($active) {
    $this->active = $active;
    return $this;
  }
  
  /**
   * @return Psc\DateTime\DateTime
   */
  public function getCreated() {
    return $this->created;
  }
  
  /**
   * @param Psc\DateTime\DateTime $created
   */
  public function setCreated(DateTime $created) {
    $this->created = $created;
    return $this;
  }
  
  /**
   * @return Psc\DateTime\DateTime
   */
  public function getModified() {
    return $this->modified;
  }
  
  /**
   * @param Psc\DateTime\DateTime $modified
   */
  public function setModified(DateTime $modified = NULL) {
    $this->modified = $modified;
    return $this;
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Psc\Entities\NavigationNode>
   */
  public function getNavigationNodes() {
    return $this->navigationNodes;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Psc\Entities\NavigationNode> $navigationNodes
   */
  public function setNavigationNodes(Collection $navigationNodes) {
    $this->navigationNodes = $navigationNodes;
    return $this;
  }
  
  /**
   * @param Psc\Entities\NavigationNode $navigationNode
   * @chainable
   */
  public function addNavigationNode(NavigationNode $navigationNode) {
    if (!$this->navigationNodes->contains($navigationNode)) {
      $this->navigationNodes->add($navigationNode);
    }
    return $this;
  }
  
  /**
   * @param Psc\Entities\NavigationNode $navigationNode
   * @chainable
   */
  public function removeNavigationNode(NavigationNode $navigationNode) {
    if ($this->navigationNodes->contains($navigationNode)) {
      $this->navigationNodes->removeElement($navigationNode);
    }
    return $this;
  }
  
  /**
   * @param Psc\Entities\NavigationNode $navigationNode
   * @return bool
   */
  public function hasNavigationNode(NavigationNode $navigationNode) {
    return $this->navigationNodes->contains($navigationNode);
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Psc\Entities\ContentStream\ContentStream>
   */
  public function getContentStreams() {
    return $this->contentStreams;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Psc\Entities\ContentStream\ContentStream> $contentStreams
   */
  public function setContentStreams(Collection $contentStreams) {
    $this->contentStreams = $contentStreams;
    return $this;
  }
  
  /**
   * @param Psc\Entities\ContentStream\ContentStream $contentStream
   * @chainable
   */
  public function addContentStream(ContentStream $contentStream) {
    if (!$this->contentStreams->contains($contentStream)) {
      $this->contentStreams->add($contentStream);
    }
    return $this;
  }
  
  /**
   * @param Psc\Entities\ContentStream\ContentStream $contentStream
   * @chainable
   */
  public function removeContentStream(ContentStream $contentStream) {
    if ($this->contentStreams->contains($contentStream)) {
      $this->contentStreams->removeElement($contentStream);
    }
    return $this;
  }
  
  /**
   * @param Psc\Entities\ContentStream\ContentStream $contentStream
   * @return bool
   */
  public function hasContentStream(ContentStream $contentStream) {
    return $this->contentStreams->contains($contentStream);
  }
  
  public function getEntityName() {
    return 'Psc\Entities\CompiledPage';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Psc\Data\Type\IdType(),
      'slug' => new \Psc\Data\Type\StringType(),
      'active' => new \Psc\Data\Type\BooleanType(),
      'created' => new \Psc\Data\Type\DateTimeType(),
      'modified' => new \Psc\Data\Type\DateTimeType(),
      'navigationNodes' => new \Psc\Data\Type\PersistentCollectionType(new \Psc\Code\Generate\GClass('Psc\\Entities\\NavigationNode')),
      'contentStreams' => new \Psc\Data\Type\PersistentCollectionType(new \Psc\Code\Generate\GClass('Psc\\Entities\\ContentStream\\ContentStream')),
    ));
  }
}
?>