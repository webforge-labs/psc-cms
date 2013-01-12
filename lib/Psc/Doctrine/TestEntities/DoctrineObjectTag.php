<?php

namespace Psc\Doctrine\TestEntities;

use Psc\Data\ArrayCollection;

/**
 * @Entity(repositoryClass="Psc\Doctrine\TestEntities\TagRepository")
 * @Table(name="test_tags", uniqueConstraints={@UniqueConstraint(name="tag_label", columns={"label"})})
 */
class OldTagNotUsed extends \Psc\Doctrine\Object {
  
  /**
   * @Id
   * @GeneratedValue
   * @Column(type="integer")
   * @var int
   */
  protected $id;

  /**
   * @Column(type="string")
   * @var string
   */
  protected $label;

  /**
   * @Column(type="PscDateTime");
   * @var Psc\DateTime\DateTime
   */
  protected $created;
  
  /**
   * 
   * @ManyToMany(targetEntity="Psc\Doctrine\TestEntities\Article", mappedBy="tags")
   * @var Psc\Data\ArrayCollection<Psc\Doctrine\TestEntities\Articles>
   */
  protected $articles;

  public function __construct($label) {
    $this->label = $label;
    $this->created = \Psc\DateTime\DateTime::now();
    $this->articles = new ArrayCollection();
  }
  
  public function setId($id) {
    $this->id = $id;
    return $this;
  }
  
  public function getCreated() {
    return $this->created;
  }
  
  public function getEntityName() {
    return 'Psc\Doctrine\TestEntities\Tag';
  }
  
  public function getIdentifier() {
    return $this->id;
  }
  
  public function addArticle(Article $article) {
    if (!$this->articles->contains($article)) {
      $this->articles->add($article);
    }
    
    return $this;
  }
  
  public function removeArticle(Article $article) {
    if ($this->articles->contains($article)) {
      $this->articles->removeElement($article);
    }
    return $this;
  }
  
  public function getTabsLabel() {
    return sprintf('Tag: %s', $this->label);
  }
}
?>