<?php

namespace Psc\Doctrine\TestEntities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Doctrine\TestEntities\CategoryRepository")
 * @ORM\Table(name="test_categories", uniqueConstraints={@ORM\UniqueConstraint(name="category_label", columns={"label"})})
 */
class Category extends CompiledCategory implements \Psc\CMS\DefaultRequestableEntity {

  public function setId($id) {
    $this->id = $id;
    return $this;
  }
  
  public function getDefaultRequestMeta(\Psc\CMS\EntityMeta $entityMeta) {
    return $entityMeta->getActionRequestMeta('articles', $this); // display articles list in category
  }

  public function getEntityName() {
    return 'Psc\Doctrine\TestEntities\Category';
  }
}
?>