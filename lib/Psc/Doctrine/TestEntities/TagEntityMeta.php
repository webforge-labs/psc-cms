<?php

namespace Psc\Doctrine\TestEntities;

use Psc\CMS\TabsContentItem2;

class TagEntityMeta extends \Psc\CMS\EntityMeta {
  
  public function __construct($classMetadata) {
    parent::__construct('Psc\Doctrine\TestEntitites\Tag', $classMetadata, array(TabsContentItem2::LABEL_DEFAULT=>'Tag'));
  }
}
?>