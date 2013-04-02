<?php

namespace Psc\CMS\Controller;

use Psc\CMS\EntityMeta;
use Psc\Doctrine\EntityRepository;
use Psc\UI\PagesMenu;
use Psc\UI\FormPanel;
use Psc\CMS\Roles\Page as PageRole;

class PageControllerHelper {

  protected $defaultRevision = 'default';

  /**
   * @return array
   */
  public function getContentStreamButtons(PageRole $page, EntityMeta $contentStreamEntityMeta) {
    $buttons = array();
    foreach ($page->getContentStream()
              ->type('page-content')
              ->revision($this->defaultRevision)
              ->collection() as $contentStream) {

      $adapter = $contentStreamEntityMeta->getAdapter($contentStream, EntityMeta::CONTEXT_GRID);
      $adapter->setButtonMode(\Psc\CMS\Item\Buttonable::CLICK | \Psc\CMS\Item\Buttonable::DRAG);
      $adapter->setTabLabel('Seiteninhalt: '.$page->getSlug().' ('.mb_strtoupper($contentStream->getLocale()).')');
        
      $button = $adapter->getTabButton();
        
      if ($lc = $contentStream->getLocale()) {
        $button->setLabel('Seiteninhalt für '.mb_strtoupper($lc).' bearbeiten');
      } else {
        $button->setLabel('Seiteninhalt #'.$contentStream->getIdentifier().' bearbeiten');
      }
      
      $button->setLeftIcon('pencil');
      $buttons[] = $button;
    }

    $sideBarStreams = $page->getContentStream()
      ->type('sidebar-content')
      ->revision($this->defaultRevision)
      ->collection();

    if (count($sideBarStreams) > 0) {
      $buttons[] = '<br /><br />';

      foreach ($sideBarStreams as $contentStream) {
        $adapter = $contentStreamEntityMeta->getAdapter($contentStream, EntityMeta::CONTEXT_GRID);
        $adapter->setButtonMode(\Psc\CMS\Item\Buttonable::CLICK | \Psc\CMS\Item\Buttonable::DRAG);
        $adapter->setTabLabel('Sidebar: '.$page->getSlug().' ('.mb_strtoupper($contentStream->getLocale()).')');
          
        $button = $adapter->getTabButton();
          
        if ($lc = $contentStream->getLocale()) {
          $button->setLabel('Sidebar '.$contentStream->getLocale().' bearbeiten');
        }
        
        $button->setLeftIcon('pencil');
        $button->setRightIcon('grip-dotted-vertical');
        $buttons[] = $button;
      }
    }


    return $buttons;
  }
}
?>