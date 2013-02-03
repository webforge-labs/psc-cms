<?php

namespace Psc\CMS;

use Psc\Code\Code;

/**
 * http://wiki.ps-webforge.com/psc-cms:dokumentation:actions
 */
class ActionMeta extends \Psc\SimpleObject {
  
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
   * @var string
   */
  protected $subResource;
  
  /**
   * @param Entity|EntityMeta $entityOrMete if entity the type will be specific, if entitymeta the type will be general
   */
  public function __construct($type, $verb, $subResource = NULL) {
    $this->setType($type);
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
  
  /**
   * @return bool
   */
  public function isSpecific() {
    return $this->type === self::SPECIFIC;
  }
  
  /**
   * @return bool
   */
  public function isGeneral() {
    return $this->type === self::GENERAL;
  }
  
  /**
   * @chainable
   */
  protected function setVerb($verb) {
    Code::value($verb, self::POST, self::PUT, self::GET, self::DELETE);
    $this->verb = $verb;
    return $this;
  }
  
  /**
   * @chainable
   */
  protected function setType($type) {
    Code::value($type, self::SPECIFIC, self::GENERAL);
    $this->type = $type;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getSubResource() {
    return $this->subResource;
  }
  
  /**
   * @return string
   */
  public function hasSubResource() {
    return $this->subResource !== NULL;
  }
  
  /**
   * @return const
   */
  public function getVerb() {
    return $this->verb;
  }
}
?>