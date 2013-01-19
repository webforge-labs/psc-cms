<?php

namespace Psc\CMS;

use Psc\Code\Code;
use Psc\Doctrine\DCPackage;

/**
 * An Action represents a callable action for the backend for specific Entity or general EntityMeta
 *
 * @see ActionMeta for an detailed explanation of the Tuples
 */
class Action extends ActionMeta {
  
  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  /**
   * @var Psc\CMS\Entity
   */
  protected $entity;
  
  /**
   * @param Entity|EntityMeta $entityOrMete if entity the type will be specific, if entitymeta the type will be general
   */
  public function __construct($entityOrMeta, $verb, $subResource = NULL) {
    if ($entityOrMeta instanceof EntityMeta) {
      $this->entityMeta = $entityOrMeta;
      $type = self::GENERAL;
    } elseif ($entityOrMeta instanceof Entity) {
      $this->entity = $entityOrMeta;
      $type = self::SPECIFIC;
    } else {
      throw $this->invalidArgument(1, $entityOrMeta, array('Object<Psc\CMS\EntityMeta>', 'Object<Psc\CMS\Entity>'), __FUNCTION__);
    }
    
    parent::__construct($type, $verb, $subResource);
  }
  
  /**
   * Returns the EntityMeta for specific or general type
   *
   * Attention: When type is specific $dc is not optional!
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta(DCPackage $dc = NULL) {
    if (!isset($this->entityMeta) && $this->type === self::SPECIFIC) {
      if (!isset($dc)) throw new \LogicException('Missing Parameter 1 (DCPackage $dc) for '.__FUNCTION__);
      
      $this->entityMeta = $dc->getEntityMeta($this->entity->getEntityName());
    }
    
    return $this->entityMeta;
  }

  /**
   * @return Psc\CMS\Entity
   */
  public function getEntity() {
    return $this->entity;
  }
}
?>