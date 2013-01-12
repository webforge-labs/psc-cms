<?php

namespace Psc\CMS;

use Psc\FE\Helper AS h;
use Psc\Doctrine\DCPackage;

class AssociationsLister extends \Psc\SimpleObject {
  
  protected $lists = array();
  
  protected $entity;
  
  protected $dc;
  
  public function __construct(DCPackage $dc) {
    $this->dc = $dc;
  }
  
  public function setEntity(Entity $entity) {
    $this->entity = $entity;
    return $this;
  }
  
  /**
   * @return Psc\CMS\AssociationList
   */
  public function listAssociation($propertyName, $format) {
    $assoc = new AssociationList($propertyName, $format);
    $this->lists = $assoc;
    
    return $assoc;
  }
  
  /**
   * Gibt das HTML für die AssociationList zurück
   *
   * ist $assoc->limit angegeben und ist die anzahl der entities größer als diess wird $assoc->limitMessage zurückgegeben
   * sind keine Entities zu finden, wird $assoc->emptyText zurückgegeben
   * ansonsten wird für jedes Entities $assoc.withLabel oder ein button erzeugt (withButton(TRUE))
   */
  public function getHTMLForList(AssociationList $assoc) {
    if (!isset($this->entity)) {
      throw new \RuntimeException('Entity muss gesetzt sein, wenn html() oder init() aufgerufen wird');
    }
    
    $entities = $this->getAssocEntities($assoc); // die collection, ist klar
    
    if (($sorter = $assoc->getSortBy()) != NULL) {
      $entities = $sorter($entities);
    }
    
    $cnt = count($entities);
    
    if ($cnt > 0) {
      if ($assoc->hasLimit() && $cnt > $assoc->getLimit()) {
        return sprintf($assoc->getLimitMessage(), $cnt);
      } else {
        $html = h::listObjects(
          $entities,
          $assoc->getJoinedWith(), // zwischen allen items (außer dem vorletzten + letzten wenn andjoiner gesett)
          $assoc->getWithButton() ? $this->getHTMLButtonClosure() : $assoc->getWithLabel(), // html-closure für das foreign-entity
          $assoc->getAndJoiner() // zwischen dem vorletzten + letzten item
          // $formatter nicht angegeben
        );
      }
      return \Psc\TPL\TPL::miniTemplate($assoc->getFormat(), array('list'=>$html));
    } else {
      return $assoc->getEmptyText();
    }
  }
  
  protected function getHTMLButtonClosure() {
    $dc = $this->dc;
    return function (Entity $entity) use ($dc) {
      
      $adapter = $dc->getEntityMeta($entity->getEntityName())->getAdapter($entity, EntityMeta::CONTEXT_ASSOC_LIST);
      $adapter->setButtonMode(\Psc\CMS\Item\Buttonable::CLICK | \Psc\CMS\Item\Buttonable::DRAG);
      
      $button = $adapter->getTabButton();
      
      return $button;
    };
  }
  
  public function getListsHTML(Array $associations) {
    $html = array();
    foreach ($associations as $association) {
      $html[] = $this->getHTMLForList($association);
    }
    return $html;
  }
  
  protected function getAssocEntities(AssociationList $assoc) {
    if ($assoc->hasEntities()) {
      return $assoc->getEntities();
    } else {
      return $this->entity->callGetter($assoc->getPropertyName());
    }
  }
  
  public function getEntity() {
    return $this->entity;
  }
}
?>