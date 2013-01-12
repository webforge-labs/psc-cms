<?php

namespace Psc\CMS;

use Psc\Doctrine\DCPackage;
use Psc\UI\DropContentsList;
use Psc\UI\Accordion;

class RightContent extends \Psc\SimpleObject implements \Psc\CMS\DropContentsListPopulator {
  
  protected $dc;
  protected $accordion;
  
  public function __construct(DCPackage $dc) {
    $this->dc = $dc;
  }
  
  /**
   *
   * kann Psc\UI\DropContentsList $cms->newDropContentsList()
   * benutzen um neue Listen auf der Rechten seite zu erstellen (Akkordions);
   *
   */
  public function populateLists(\Psc\CMS\DropContentsListCreater $creater) {
    $cmsList = $creater->newDropContentsList('CMS');
    
    $this->addGridLink($cmsList, 'user');
  }
  
  public function getAccordion(Array $dropContents) {
    if (!isset($this->accordion)) {
      $this->accordion = new Accordion(array('autoHeight'=>false,'active'=>false,'collapsible'=>true));
      foreach ($dropContents as $label => $list) {
        $this->accordion->addSection($label, array($list->html()), Accordion::END, $list->getOpenAllIcon());
      }
    }
    return $this->accordion;
  }
  
  protected function addGridLink(DropContentsList $list, $entityName) {
    return $list->addLinkable($this->dc->getEntityMeta($entityName)->getAdapter(Item\Adapter::CONTEXT_GRID)->getRCLinkable());
  }
  
  protected function addSearchPanelLink(DropContentsList $list, $entityName) {
    return $list->addLinkable($this->dc->getEntityMeta($entityName)->getAdapter(Item\Adapter::CONTEXT_SEARCHPANEL)->getRCLinkable());
  }
  
  protected function addNewLink(DropContentsList $list, $entityName) {
    return $list->addLinkable($this->dc->getEntityMeta($entityName)->getAdapter(Item\Adapter::CONTEXT_NEW)->getRCLinkable());
  }

  protected function addEntityLink(DropContentsList $list, \Psc\CMS\Entity $entity) {
    return $list->addLinkable($this->dc->getEntityMeta($entity->getEntityName())->getAdapter($entity,Item\Adapter::CONTEXT_RIGHT_CONTENT)->getRCLinkable());
  }
}
?>