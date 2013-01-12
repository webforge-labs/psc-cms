<?php

namespace Psc\CMS;

use Psc\Code\Code;

/**
 * An Action represents a callable action for the backend
 *
 * Action can display HTML Documents (forms e.g.)
 * Save to a database (PUT JSON body with contents for saving an entity)
 * retrieve from a database and what you ever can think of
 *
 * an action consists of a tupel of these:
 *
 * - the type
 *   currently there are only two types avaible: general and specific
 * - the verb
 *   currently PUT|POST|GET|DELETE
 * - the subresource
 *   this can be null or a string like form, grid, patch something like that
 *
 * the type refers to the result of results or the effect of one ore more entities.
 * general action types always refer to a group of entities or an unkown entity and specific is always bound to a already known entity
 *
 * examples for general actions
 *   getting a list of entities
 *   searching for entities (and auto completion)
 *   showing a grid of entities
 *   inserting a new entity (this is general because its mainly putting something to a group of entities)
 *   changing the order for entities in a list
 *   batch processing
 *   
 * examples for specific actions
 *   editing the properties of an entity
 *   deleting an entity
 *   getting the edit-form of an entity
 *   patch a value from an entity
 *
 * the verb should be easy to understand, in most cases it can be used as the REST verb, allthough it should be decoupled from REST
 *
 * the subresource is used as a identifier for the action, to distinguish between different actions
 * this is handy for custom actions that cannot be clearly mapped to the REST pattern.
 * Sometimes there are several specific-GET - Actions for an entity. For example two different views in HTML
 * for the basic CRUD-actions of a controller subresource is mostly NULL
 */
class Action extends \Psc\SimpleObject {
  
  const SPECIFIC = 'specific';
  const GENERAL = 'general';
  
  const GET = 'GET';
  const POST = 'POST';
  const PUT = 'PUT';
  const DELETE = 'DELETE';
  
  /**
   * @var const
   */
  protected $type;
  
  /**
   * @var const
   */
  protected $verb;
  
  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  /**
   * @var Psc\CMS\Entity
   */
  protected $entity;
  
  /**
   * @var string
   */
  protected $subResource;
  
  public function __construct($entityOrMeta, $verb, $subResource = NULL) {
    if ($entityOrMeta instanceof EntityMeta) {
      $this->entityMeta = $entityOrMeta;
      $this->type = self::GENERAL;
    } elseif ($entityOrMeta instanceof Entity) {
      $this->entity = $entityOrMeta;
      $this->type = self::SPECIFIC;
    } else {
      throw $this->invalidArgument(1, $entityOrMeta, array('Object<Psc\CMS\EntityMeta>', 'Object<Psc\CMS\Entity>'), __FUNCTION__);
    }
    
    $this->setVerb($verb);
    $this->subResource = $subResource;
  }
  
  /**
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta() {
    return $this->entityMeta;
  }

  /**
   * @return Psc\CMS\Entity
   */
  public function getEntity() {
    return $this->entity;
  }
  
  /**
   * @return const
   */
  public function getType() {
    return $this->type;
  }
  
  public function setVerb($verb) {
    Code::value($verb, self::POST, self::PUT, self::GET, self::DELETE);
    $this->verb = $verb;
    return $this;
  }
  
  /**
   * @return const
   */
  public function getVerb() {
    return $this->verb;
  }
}
?>