<?php

namespace Psc\CMS;

use Psc\Doctrine\DCPackage;
use Psc\UI\DropContentsList;
use Psc\UI\Accordion;
use Psc\CMS\Item\RightContentLink;
use Psc\CMS\Translation\Container as TranslationContainer;

class RightContent extends \Psc\SimpleObject implements \Psc\CMS\DropContentsListPopulator {
  
  protected $dc;
  protected $accordion;

  protected $translationContainer;
  
  public function __construct(DCPackage $dc, TranslationContainer $translationContainer) {
    $this->dc = $dc;
    $this->translationContainer = $translationContainer;
  }
  
  /**
   *
   * kann Psc\UI\DropContentsList $cms->newDropContentsList()
   * benutzen um neue Listen auf der Rechten seite zu erstellen (Akkordions);
   *
   */
  public function populateLists(\Psc\CMS\DropContentsListCreater $creater) {
    $cmsList = $creater->newDropContentsList($this->translate('sidebar.cms'));
    
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

  protected function addNavigationLink(DropContentsList $list, $context, $label) {
    return $list->addLinkable(
      new RightContentLink(
        new \Psc\CMS\RequestMeta(
          'GET',
          '/entities/navigation-node/'.$context.'/form'
        ),
        $label
      )
    );
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

  protected function translate($key, Array $parameters = array()) {
    return $this->translationContainer->getTranslator()->trans($key, $parameters, 'cms');
  }
}
