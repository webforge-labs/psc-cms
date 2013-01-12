<?php

namespace Psc\Doctrine\TestEntities;

use Doctrine\Common\Collections\Collection;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledArticle extends \Psc\CMS\AbstractEntity {
  
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
  protected $title;
  
  /**
   * @var string
   * @ORM\Column(type="text")
   */
  protected $content;
  
  /**
   * @var integer
   * @ORM\Column(type="integer", nullable=true)
   */
  protected $sort;
  
  /**
   * @var Doctrine\Common\Collections\Collection<Psc\Doctrine\TestEntities\Tag>
   * @ORM\ManyToMany(targetEntity="Psc\Doctrine\TestEntities\Tag", inversedBy="articles")
   * @ORM\JoinTable(name="article2tag", joinColumns={@ORM\JoinColumn(name="article_id", onDelete="cascade")}, inverseJoinColumns={@ORM\JoinColumn(name="tag_id", onDelete="cascade")})
   */
  protected $tags;
  
  /**
   * @var Psc\Doctrine\TestEntities\Category
   * @ORM\ManyToOne(targetEntity="Psc\Doctrine\TestEntities\Category", inversedBy="articles")
   * @ORM\JoinColumn(onDelete="cascade")
   */
  protected $category;
  
  public function __construct($title, $content) {
    $this->setTitle($title);
    $this->setContent($content);
    $this->tags = new \Psc\Data\ArrayCollection();
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
  public function getTitle() {
    return $this->title;
  }
  
  /**
   * @param string $title
   */
  public function setTitle($title) {
    $this->title = $title;
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
  
  /**
   * @return integer
   */
  public function getSort() {
    return $this->sort;
  }
  
  /**
   * @param integer $sort
   */
  public function setSort($sort) {
    $this->sort = $sort;
    return $this;
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Psc\Doctrine\TestEntities\Tag>
   */
  public function getTags() {
    return $this->tags;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Psc\Doctrine\TestEntities\Tag> $tags
   */
  public function setTags(Collection $tags) {
    $this->tags = $tags;
    return $this;
  }
  
  /**
   * @param Psc\Doctrine\TestEntities\Tag $tag
   * @chainable
   */
  public function addTag(Tag $tag) {
    if (!$this->tags->contains($tag)) {
      $this->tags->add($tag);
    }
    $tag->addArticle($this);
    return $this;
  }
  
  /**
   * @param Psc\Doctrine\TestEntities\Tag $tag
   * @chainable
   */
  public function removeTag(Tag $tag) {
    if ($this->tags->contains($tag)) {
      $this->tags->removeElement($tag);
    }
    $tag->removeArticle($this);
    return $this;
  }
  
  /**
   * @param Psc\Doctrine\TestEntities\Tag $tag
   * @return bool
   */
  public function hasTag(Tag $tag) {
    return $this->tags->contains($tag);
  }
  
  /**
   * @return Psc\Doctrine\TestEntities\Category
   */
  public function getCategory() {
    return $this->category;
  }
  
  /**
   * @param Psc\Doctrine\TestEntities\Category $category
   */
  public function setCategory(Category $category = NULL) {
    $this->category = $category;
    if (isset($category)) $category->addArticle($this);

    return $this;
  }
  
  public function getEntityName() {
    return 'Psc\Doctrine\TestEntities\CompiledArticle';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Psc\Data\Type\IdType(),
      'title' => new \Psc\Data\Type\StringType(),
      'content' => new \Psc\Data\Type\MarkupTextType(),
      'sort' => new \Psc\Data\Type\IntegerType(),
      'tags' => new \Psc\Data\Type\PersistentCollectionType(new \Psc\Code\Generate\GClass('Psc\\Doctrine\\TestEntities\\Tag')),
      'category' => new \Psc\Data\Type\EntityType(new \Psc\Code\Generate\GClass('Psc\\Doctrine\\TestEntities\\Category')),
    ));
  }
}
?>