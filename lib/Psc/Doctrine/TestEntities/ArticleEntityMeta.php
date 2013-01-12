<?php

namespace Psc\Doctrine\TestEntities;

class ArticleEntityMeta extends \Psc\CMS\EntityMeta {
  
  public function __construct($classMetadata = NULL) {
    parent::__construct('Psc\Doctrine\TestEntitites\Article',
                        $classMetadata ?: new \Doctrine\ORM\Mapping\ClassMetadata('Entities\Article'),
                        array('default'=>'Artikel'));
  }
}
?>