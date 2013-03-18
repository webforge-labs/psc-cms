<?php

namespace Psc\CMS\Controller;

use Psc\CMS\EntityMeta;
use Psc\Doctrine\EntityRepository;
use Psc\UI\PagesMenu;
use Psc\UI\FormPanel;
use Psc\CMS\Roles\Page as PageRole;

class PageControllerHelper {

  protected $defaultRevision = 'default';

  public function getPagesMenuPanel(EntityRepository $navigationRepository, Array $languages, EntityMeta $entityMeta) {
    $menu = new PagesMenu(
     $navigationRepository->setContext('default')->getFlatForUI('de', $languages)
    );

    $footerMenu = new PagesMenu(
      $navigationRepository->setContext('footer')->getFlatForUI('de', $languages)
    );

    $topMenu = new PagesMenu(
      $navigationRepository->setContext('top')->getFlatForUI('de', $languages)
    );
    
    $panel = new FormPanel('Seiten Übersicht');
    $panel->setPanelButtons(array('reload'));
    $panel->getPanelButtons()->addNewButton(
      $entityMeta->getAdapter()->getNewTabButton()
    );
    $panel->setWidth(100);
    $panel->addContent($topMenu->html());
    $panel->addContent($menu->html()->setStyle('margin-top', '80px'));
    $panel->addContent($footerMenu->html()->setStyle('margin-top', '150px'));

    return $panel;
  }

  /**
   * @return array
   */
  public function getContentStreamButtons(PageRole $page, EntityMeta $contentStreamEntityMeta) {
    $buttons = array();
    foreach ($page->getContentStreamsByRevision($this->defaultRevision) as $contentStream) {
      $adapter = $contentStreamEntityMeta->getAdapter($contentStream, EntityMeta::CONTEXT_GRID);
      $adapter->setButtonMode(\Psc\CMS\Item\Buttonable::CLICK | \Psc\CMS\Item\Buttonable::DRAG);
      $adapter->setTabLabel('Seiteninhalt: '.$page->getSlug().' ('.mb_strtoupper($contentStream->getLocale()).')');
        
      $button = $adapter->getTabButton();
        
      if ($lc = $contentStream->getLocale()) {
        $button->setLabel('Seiteninhalt für '.mb_strtoupper($lc).' bearbeiten');
      } else {
        $button->setLabel('Seiteninhalt #'.$contentStream->getIdentifier().' bearbeiten');
      }
      
      $button->setLeftIcon('wrench');
      $buttons[] = $button;
    }
    return $buttons;
  }
}
?>