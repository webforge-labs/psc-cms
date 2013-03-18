<?php

namespace Psc\CMS;

use Psc\UI\HTML as HTML;

class EntityForm extends ComponentsForm {
  
  protected $entity;
  
  /**
   * The revision of the form, which matches the current (loaded)-data
   *
   * the revision can be used to identify the data set which is represented in the form
   * the revision field will be put into the form as
   *  X-Psc-Cms-Revision
   * header
   * @var string not yet specified format 'default' when no specific revision is set
   */
  protected $revision;
  
  /**
   * @TODO vom Entity wird bis jetzt nur identifyable benutzt
   * @param string $revision the revision of the form, which matches the current (loaded)-data
   */
  public function __construct(Entity $entity, RequestMeta $requestMeta, $revision = 'default') {
    $this->setRequestMeta($requestMeta);
    $this->entity = $entity;
    $this->setRevision($revision);
    
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
  
  /**
   * @return string
   */
  public function getRevision() {
    return $this->revision;
  }
  
  protected function setRevision($revision) {
    $this->revision = $revision;
    $this->setHTTPHeader('X-Psc-Cms-Revision', $revision);
    return $this;
  }
}
?>