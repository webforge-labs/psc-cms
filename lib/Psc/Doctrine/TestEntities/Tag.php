<?php

namespace Psc\Doctrine\TestEntities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Doctrine\TestEntities\TagRepository")
 * @ORM\Table(name="test_tags", uniqueConstraints={@ORM\UniqueConstraint(name="tag_label", columns={"label"})})
 */
class Tag extends CompiledTag {

  public function __construct($label) {
    $this->label = $label;
    $this->created = \Psc\DateTime\DateTime::now();
    $this->articles = new ArrayCollection();
  }
  
  public function setId($id) {
    $this->id = $id;
    return $this;
  }

  public function getEntityName() {
    return 'Psc\Doctrine\TestEntities\Tag';
  }
  
  public function getContextLabel($context = self::CONTEXT_DEFAULT) {
    return sprintf('Tag: %s', $this->label);
  }
}
?>