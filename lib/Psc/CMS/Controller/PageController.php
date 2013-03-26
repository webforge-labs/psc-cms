<?php

namespace Psc\CMS\Controller;

use Psc\CMS\Roles\Page as PageRole;
use Psc\CMS\Entity;
use Psc\CMS\EntityMeta;
use Psc\CMS\Controller\PageControllerHelper;
use Psc\Code\Code;
use stdClass as FormData;
use Psc\CMS\Roles\SimpleContainer as SimpleContainerRole;
use Doctrine\ORM\EntityManager;
use Psc\UI\FormPanel;

abstract class PageController extends ContainerController {

  /**
   * @var Psc\Doctrine\EntityRepository
   */
  protected $navigationRepository;
  
  protected function setUp() {
    // achtung: hier sind languages und language noch nicht defined! (erst nach setup)
    parent::setUp();
    $this->setPropertiesOrder(array('slug', 'navigationNodes', 'active'));

    $this->addBlackListProperties(array('contentStreams', 'created', 'modified'), 'form');
    $this->addBlackListProperties(array('contentStreams', 'created', 'modified'), 'grid');

    $this->addOptionalProperty('contentStreams');

    $this->getEntitymeta()->setPropertiesHints(
      array(
        'slug'=>
          'Die technische Kurzbezeichnung der Seite sollte möglichst kurz und prägnant sein',
        'navigationNodes'=>
          'Die Seite muss einem Navigations-Punkt zugeordnet werden, sonst ist sie auf der öffentlichen Webseite nicht zu sehen.'."\n".
          'Normalerweise hat jede Seite genau einen Navigations-Punkt. Navigations-Punkte können in „Navigation Pflegen“ verwaltet werden.',
        'active'=>
          "Soll die Seite öffentlich angezeigt werden?\nAchtung: dies blendet auch mögliche Unterpunkte der Seite aus, wenn deaktiviert."
      )
    );
    
    
    $this->helper = new PageControllerHelper();
  }
    
  protected function initLabeler(\Psc\CMS\Labeler $labeler) {
    $labeler
      ->label('slug', 'Kurzname')
      ->label('contentStreams','Layouts')
      ->label('modified','zuletzt bearbeitet')
      ->label('navigationNodes','verknüpfte Navigations-Punkte')
      ->label('active', 'aktiv')
      ->label('commentsAllowed','Kommentare erlauben')
    ;
  }

  public function getEntityGrid(EntityMeta $entityMeta, $entities) {
    $navController = $this->getController('NavigationNode');

    $menu = $navController->getPagesMenu('default');
    $footerMenu = $navController->getPagesMenu('footer');
    $topMenu = $navController->getPagesMenu('top');
    
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
  
  public function getEntityFormular(Entity $entity) {
    $page = $entity;
    $panel = parent::getEntityFormular($page);
    
    $buttons = $this->helper->getContentStreamButtons($page, $this->dc->getEntityMeta('CoMun\Entities\ContentStream\ContentStream'));
    $panel->getRightAccordion()->addSection('Inhalte', $buttons, \Psc\UI\Accordion::START);
    
    return $panel;
  }


  /**
   * Beim ersten Speichern eines neuen Entities fügen wir die Content-Streams (die wir vorhe rnicht anzeigen) hinzu
   * 
   */
  protected function processEntityFormRequest(Entity $entity, FormData $requestData, $revision) {
    $this->addOptionalProperty('contentStreams');
    
    // erst formular bearbeiten
    parent::processEntityFormRequest($entity, $requestData, $revision);
    
    // wenn es neu ist, wollen wir die content streams erstellen
    if ($entity->isNew()) {
      $this->fillContentStreams($entity);
    }
  }

  /**
   * @return PageRole
   */
  public function createInactivePage($slug) {
    $page = $this->createEmptyEntity();
    $page->setSlug($slug);
    $page->setActive(FALSE);
    $this->fillContentStreams($page);

    return $page;
  }

  /**
   * Returns an Empty Page with the slug "new-page"
   * 
   * @return PageRole
   */
  public function createEmptyEntity($revision = NULL) {
    $page = $this->container->getRoleFQN('Page');
    return new $page('new-page');
  }

  protected function fillContentStreams(PageRole $page) {
    $streams = $page->getContentStreamsByLocale();
    $csClass = $this->container->getRoleFQN('ContentStream');
    
    // per default haben wir immer einen content-stream pro sprache
    foreach ($this->container->getLanguages() as $lang) {
      if (!array_key_exists($lang, $streams)) {
        $cs = new $csClass($lang);
        $page->addContentStream($cs);
        $this->dc->getEntityManager()->persist($cs);
      }
    }
  }
  
  protected function initProcessor(\Psc\Doctrine\Processor $processor) {
    $processor->setSynchronizeCollections('normal');
  }
  
  public function getEntity($identifier, $subResource = NULL, $query = NULL) {
    if ($subResource === 'web') {
      $page = parent::getEntity($identifier, NULL, $query);
      
      return $this->getWebHTML($page, $query);
    } elseif(is_array($subResource) && $subResource[0] === 'contentstream') {
      $page = parent::getEntity($identifier, NULL, $query);
      $contentStream = $page->getContentStreamByLocale($subResource[1]);
      return $this->getController('ContentStream')->getEntityFormular($contentStream);

    } else {
      return parent::getEntity($identifier, $subResource, $query);
    }
  }

  
  //abstract public function getWebHTML(PageRole $page, Array $query = NULL);


  protected function hydrateEntityInRevision($identifier, $revision) { // slug oder identifier geht beides
    return $this->repository->hydrate($identifier);
  }

  protected function getNavigationRepository() {
    if (!isset($this->navigationRepository)) {
      $this->navigationRepository = $this->dc->getRepository($this->container->getRoleFQN('NavigationNode'));
    }
    return $this->navigationRepository;
  }

  protected function getNavigationController() {

  }

  
  /**
   * @return Array
   */
  public function getLanguages() {
    return $this->container->getLanguages();
  }

  /**
   * @return string
   */
  public function getLanguage() {
    return $this->container->getLanguage();
  }
}
