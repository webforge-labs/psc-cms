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