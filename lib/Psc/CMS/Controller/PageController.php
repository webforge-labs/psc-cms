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

abstract class PageController extends SimpleContainerController {

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
    return $this->helper->getPagesMenuPanel($this->getNavigationRepository(), $this->getLanguages(), $entityMeta);
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
      self::fillContentStreams($entity, $this->dc->getEntityManager(), $this->container);
    }
  }

  public static function fillContentStreams(PageRole $page, EntityManager $em, SimpleContainerRole $container) {
    $streams = $page->getContentStreamsByLocale();
    $csClass = $container->getRoleFQN('ContentStream');
    
    // per default haben wir immer einen content-stream pro sprache
    foreach ($container->getLanguages() as $lang) {
      if (!array_key_exists($lang, $streams)) {
        $cs = new $csClass($lang);
        $page->addContentStream($cs);
        $em->persist($cs);
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
