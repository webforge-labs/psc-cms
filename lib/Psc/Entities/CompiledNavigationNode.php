<?php

namespace Psc\Entities;

use Webforge\Common\DateTime\DateTime;
use Doctrine\Common\Collections\Collection;
use Psc\Entities\ContentStream\ContentStream;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledNavigationNode extends \Psc\CMS\Roles\NavigationNodeEntity {
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  /**
   * @var array
   */
  protected $i18nTitle = array();
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $titleDe;
  
  /**
   * @var array
   */
  protected $i18nSlug = array();
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $slugDe;
  
  /**
   * @var integer
   * @ORM\Column(type="integer")
   */
  protected $lft;
  
  /**
   * @var integer
   * @ORM\Column(type="integer")
   */
  protected $rgt;
  
  /**
   * @var integer
   * @ORM\Column(type="integer")
   */
  protected $depth;
  
  /**
   * @var string
   * @ORM\Column(nullable=true)
   */
  protected $image;
  
  /**
   * @var Webforge\Common\DateTime\DateTime
   * @ORM\Column(type="WebforgeDateTime")
   */
  protected $created;
  
  /**
   * @var Webforge\Common\DateTime\DateTime
   * @ORM\Column(type="WebforgeDateTime")
   */
  protected $updated;
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $context = 'default';
  
  /**
   * @var Psc\Entities\Page
   * @ORM\ManyToOne(targetEntity="Psc\Entities\Page", inversedBy="navigationNodes")
   * @ORM\JoinColumn(onDelete="SET NULL")
   */
  protected $page;
  
  /**
   * @var Doctrine\Common\Collections\Collection<Psc\Entities\NavigationNode>
   * @ORM\OneToMany(mappedBy="parent", targetEntity="Psc\Entities\NavigationNode")
   * @ORM\OrderBy({"lft"="ASC"})
   */
  protected $children;
  
  /**
   * @var Psc\Entities\NavigationNode
   * @ORM\ManyToOne(targetEntity="Psc\Entities\NavigationNode", inversedBy="children")
   * @ORM\JoinColumn(onDelete="SET NULL")
   */
  protected $parent;
  
  /**
   * @var Doctrine\Common\Collections\Collection<Psc\Entities\ContentStream\ContentStream>
   * @ORM\ManyToMany(targetEntity="Psc\Entities\ContentStream\ContentStream")
   * @ORM\JoinTable(name="navigationnode2contentstream", joinColumns={@ORM\JoinColumn(name="navigationnode_id", onDelete="cascade")}, inverseJoinColumns={@ORM\JoinColumn(name="contentstream_id", onDelete="cascade")})
   */
  protected $contentStreams;
  
  public function __construct() {
    $this->children = new \Psc\Data\ArrayCollection();
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
  
  /**
   * @return array
   */
  public function getI18nTitle() {
    if (count($this->i18nTitle) == 0) {
      $this->i18nTitle['de'] = $this->titleDe;
    }
    return $this->i18nTitle;
  }
  
  /**
   * @param array $i18nTitle
   */
  public function setI18nTitle(Array $i18nTitle) {
    $this->i18nTitle = $i18nTitle;
    $this->titleDe = $this->i18nTitle['de'];
    return $this;
  }
  
  public function getTitle($lang) {
    $title = $this->getI18nTitle();
    return $title[$lang];
  }
  
  public function setTitle($value, $lang) {
    if ($lang === 'de') {
      $this->titleDe = $this->i18nTitle['de'] = $value;
    }
    return $this;
  }
  
  /**
   * @return array
   */
  public function getI18nSlug() {
    if (count($this->i18nSlug) == 0) {
      $this->i18nSlug['de'] = $this->slugDe;
    }
    return $this->i18nSlug;
  }
  
  /**
   * @param array $i18nSlug
   */
  public function setI18nSlug(Array $i18nSlug) {
    $this->i18nSlug = $i18nSlug;
    $this->slugDe = $this->i18nSlug['de'];
    return $this;
  }
  
  public function getSlug($lang) {
    $title = $this->getI18nSlug();
    return $title[$lang];
  }
  
  public function setSlug($value, $lang) {
    if ($lang === 'de') {
      $this->slugDe = $this->i18nSlug['de'] = $value;
    }
    return $this;
  }
  
  public function getLft() {
    return $this->lft;
  }
  
  public function setLft($lft) {
    $this->lft = $lft;
    return $this;
  }
  
  public function getRgt() {
    return $this->rgt;
  }
  
  public function setRgt($rgt) {
    $this->rgt = $rgt;
    return $this;
  }
  
  public function getDepth() {
    return $this->depth;
  }
  
  public function setDepth($depth) {
    $this->depth = $depth;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getImage() {
    return $this->image;
  }
  
  /**
   * @param string $image
   */
  public function setImage($image) {
    $this->image = $image;
    return $this;
  }
  
  /**
   * @return Webforge\Common\DateTime\DateTime
   */
  public function getCreated() {
    return $this->created;
  }
  
  /**
   * @param Webforge\Common\DateTime\DateTime $created
   */
  public function setCreated(DateTime $created) {
    $this->created = $created;
    return $this;
  }
  
  /**
   * @return Webforge\Common\DateTime\DateTime
   */
  public function getUpdated() {
    return $this->updated;
  }
  
  /**
   * @param Webforge\Common\DateTime\DateTime $updated
   */
  public function setUpdated(DateTime $updated) {
    $this->updated = $updated;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getContext() {
    return $this->context;
  }
  
  /**
   * @param string $context
   */
  public function setContext($context) {
    $this->context = $context;
    return $this;
  }
  
  /**
   * @return Psc\Entities\Page
   */
  public function getPage() {
    return $this->page;
  }
  
  /**
   * @param Psc\Entities\Page $page
   */
  public function setPage(Page $page = NULL) {
    $this->page = $page;
    if (isset($page)) $page->addNavigationNode($this);

    return $this;
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Psc\Entities\NavigationNode>
   */
  public function getChildren() {
    return $this->children;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Psc\Entities\NavigationNode> $children
   */
  public function setChildren(Collection $children) {
    $this->children = $children;
    return $this;
  }
  
  /**
   * @param Psc\Entities\NavigationNode $child
   * @chainable
   */
  public function addChild(NavigationNode $child) {
    if (!$this->children->contains($child)) {
      $this->children->add($child);
    }
    return $this;
  }
  
  /**
   * @param Psc\Entities\NavigationNode $child
   * @chainable
   */
  public function removeChild(NavigationNode $child) {
    if ($this->children->contains($child)) {
      $this->children->removeElement($child);
    }
    return $this;
  }
  
  /**
   * @param Psc\Entities\NavigationNode $child
   * @return bool
   */
  public function hasChild(NavigationNode $child) {
    return $this->children->contains($child);
  }
  
  /**
   * @return Psc\Entities\NavigationNode
   */
  public function getParent() {
    return $this->parent;
  }
  
  /**
   * @param Psc\Entities\NavigationNode $parent
   */
  public function setParent(NavigationNode $parent = NULL) {
    $this->parent = $parent;
    return $this;
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
    return 'Psc\Entities\CompiledNavigationNode';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Webforge\Types\IdType(),
      'i18nTitle' => new \Webforge\Types\I18nType(new \Webforge\Types\StringType(), array('de')),
      'i18nSlug' => new \Webforge\Types\I18nType(new \Webforge\Types\StringType(), array('de')),
      'lft' => new \Webforge\Types\PositiveIntegerType(),
      'rgt' => new \Webforge\Types\PositiveIntegerType(),
      'depth' => new \Webforge\Types\PositiveIntegerType(),
      'image' => new \Webforge\Types\ImageType(),
      'created' => new \Webforge\Types\DateTimeType(),
      'updated' => new \Webforge\Types\DateTimeType(),
      'context' => new \Webforge\Types\StringType(),
      'page' => new \Webforge\Types\EntityType(new \Psc\Code\Generate\GClass('Psc\\Entities\\Page')),
      'children' => new \Webforge\Types\PersistentCollectionType(new \Psc\Code\Generate\GClass('Psc\\Entities\\NavigationNode')),
      'parent' => new \Webforge\Types\EntityType(new \Psc\Code\Generate\GClass('Psc\\Entities\\NavigationNode')),
      'contentStreams' => new \Webforge\Types\PersistentCollectionType(new \Psc\Code\Generate\GClass('Psc\\Entities\\ContentStream\\ContentStream')),
    ));
  }
}
?>