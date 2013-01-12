<?php

namespace Psc\Data\Type;

use Psc\Code\Generate\GClass;

class PersistentCollectionType extends \Psc\Data\Type\CollectionType {
  
  public function __construct(GClass $entityClass) {
    parent::__construct(self::PSC_ARRAY_COLLECTION,
                        new EntityType($entityClass)
                        );
  }
}
?>