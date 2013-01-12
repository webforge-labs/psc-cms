<?php

namespace Psc\Doctrine\TestEntities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="test_articles")
 */
class Article extends CompiledArticle {

  public function getEntityName() {
    return 'Psc\Doctrine\TestEntities\Article';
  }

  public function setId($id) {
    $this->id = $id;
    return $this;
  }
  
  public function getTabsLabel($context = 'default') {
    return 'Article '.$this->getTitle();
  }
}