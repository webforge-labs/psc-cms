<?php

namespace Psc\CMS;

use Psc\UI\HTML as HTML;

class EntityForm extends ComponentsForm {
  
  protected $entity;
  
  /**
   * @TODO vom Entity wird bis jetzt nur identifyable benutzt
   */
  public function __construct(Entity $entity, RequestMeta $requestMeta) {
    $this->setRequestMeta($requestMeta);
    $this->entity = $entity;
    
    parent::__construct(spl_object_hash($requestMeta));
  }
  
  /**
   * @return array
   */
  public function getControlFields() {
    return array_merge(parent::getControlFields(), array());
  }
  
  protected function doInit() {
    parent::doInit();
    
    $this->html
      ->addClass(HTML::class2html($this->entity->getEntityName()).'-form')
      ->addClass('\Psc\entity-form');
  }
  
  /**
   * @return Psc\CMS\Entity
   */
  public function getEntity() {
    return $this->entity;
  }
}
?>