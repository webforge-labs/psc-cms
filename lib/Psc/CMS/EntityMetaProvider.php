<?php

namespace Psc\CMS;

interface EntityMetaProvider {
  
  /**
   * Returns the EntityMeta for an entity
   * 
   * @param string $entityName the FQN or the shortname of the entity
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta($entityName);
}
?>