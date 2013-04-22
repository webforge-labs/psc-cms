<?php

namespace Psc\CMS\Controller;

use stdClass AS FormData;
use Psc\Doctrine\EntityNotFoundException;
use Psc\CMS\UploadManager;
use Psc\CMS\Entity;
use Psc\UI\LayoutManager;
use Psc\UI\UploadService;
use Psc\Doctrine\EntityFactory;
use Psc\Net\Service\LinkRelation;
use Psc\TPL\ContentStream\ContentStream;
use Psc\CMS\Roles\ContentStreamAware;
use Psc\TPL\ContentStream\NoContentStreamsFoundException;

abstract class ContentStreamController extends \Psc\CMS\Controller\ContainerController {
  
  protected function getLinkRelationsForEntity(Entity $entity) {
    try {
      $page = $this->dc->getRepository($this->container->getRoleFQN('Page'))
        ->hydrateByContentStream($entity);
        
      $navigationRepository = $this->dc->getRepository($this->container->getRoleFQN('NavigationNode'));
        
      return array(
        new LinkRelation(
          'view',
          
          $this->getBaseUrl()
            ->addRelativeUrl(
              $navigationRepository->getUrl($page->getPrimaryNavigationNode(), $entity->getLocale())
            )
        )
      );

    } catch (EntityNotFoundException $e) {
      return array();
    }
  }
  
  
  protected function createNewRevisionFrom(Entity $contentStream, $revision) {
    /* wir wissen, dass wir niemals eine revision in der DB haben, die wir laden wollen (weil wir immer eine neue revision speichern, bei jedem click auf preview)
     *
     * deshalb müssen wir hier auch keinen roundtrip zur db machen (das ist natürlich inhaltlich eigentlich falsch)
    */
    $revisionContentStream = parent::createNewRevisionFrom($contentStream, $revision);
    $revisionContentStream->setLocale($contentStream->getLocale());
    $revisionContentStream->setSlug($contentStream->getSlug().':'.$revision);
    $revisionContentStream->setType($contentStream->getType());

    try {
      $page = $this->dc->getRepository($this->container->getRoleFQN('Page'))->hydrateByContentStream($contentStream);
      $page->addContentStream($revisionContentStream);
    } catch (EntityNotFoundException $e) {
    }
    
    return $revisionContentStream;
  }

  
  /**
   * Überschreibt die AbstractEntityController FUnktion die den FormPanel abspeichert
   * 
   */
  protected function processEntityFormRequest(Entity $entity, FormData $requestData, $revision) {
    $dataName = 'layoutManager';
    
    if (isset($requestData->$dataName) && count($requestData->$dataName) > 0) {
      $serialized = $requestData->$dataName;
    } /*else {
      throw $this->err->validationError($dataName, NULL, new \Psc\Exception('Das Layout muss mindestens 1 Element enthalten'));
    }*/
    
    // da wir keine unique constraints haben und neu sortieren müssen , nehmen wir die holzhammer methode:
    // delete all
    foreach ($entity->getEntries() as $entry) {
      // den entry selbst löschen
      $this->repository->remove($entry);
    }
    // auch aus dem CS löschen, weil der sonst automatisch persisted und das remove oben keinen effect hat
    $entity->getEntries()->clear();

    /*
     PS: im Moment werden Entries von ContentStreams innerhalb des ContentStreams nicht mit delete all gelöscht
     d.h. hier bleiben relativ viele leichen übrig (auch contentstreams selbst). Bei unserialize wird im 
     sub-ContentStream einfach eine neue instanz erzeugt die dann automatisch persisted wird
    */
    
    if (isset($serialized)) {
      // persist new
      $this->getContentStreamConverter()->convertUnserialized($requestData->$dataName, $entity);
    }
  }

  
  public function getEntityFormular(Entity $entity) {
    $this->init(array('ev.componentMapper', 'ev.labeler'));

    $panel = $this->createFormPanel(
      $entity, 
      sprintf(
        $entity->getType() === 'sidebar-content' ? 'Sidebar bearbeiten%s ' : 'Inhalte bearbeiten%s ',
        '' //$entity
      )
    );
    $this->initFormPanel($panel);
    $panel->removeRightAccordion();
    
    $layoutManager = new LayoutManager($this->getUploadService(), $this->getContentStreamConverter()->convertSerialized($entity));
    $this->initLayoutManagerControls($layoutManager, $entity);

    /* for simplicity we'll request a new navigation flat here 
      this allows us to refresh the state of the UI everytime we reload the php here

      later on this become an asynchron ajax service which caches the flat array and returns it to components requesting it
    */
    $layoutManager->hackFlatNavigationInjection(
      $this->container
        ->getController('NavigationNode')
          ->getMergedFlatForUI(
            $this->container->getLanguage(), 
            $this->container->getLanguages()
          )
    );
    
    
    $panel->addContent($layoutManager);
    
    return $panel;
  }


  public function prepareFor(ContentStreamAware $entity, $type, $locale, $revision = 'default') {
    try {
      $contentStream = 
         $entity->getContentStream()
           ->locale($locale)
           ->type($type)
           ->revision($revision)
           ->one()
       ;
     } catch (NoContentStreamsFoundException $e) {
       $contentStream = 
         $this->createEmptyEntity($revision)
           ->setLocale($locale)
           ->setRevision($revision)
           ->setType($type);

       $entity->addContentStream($contentStream);
       $this->repository->persist($entity);
       $this->repository->save($contentStream);
     }

    return $contentStream;
  }

  public function createEmptyEntity($revision = NULL) {
    $c = $this->container->getRoleFQN('ContentStream');
    return new $c(NULL, NULL, $revision);
  }  


  protected function initLayoutManagerControls(LayoutManager $layoutManager, ContentStream $contentStream) {
    $layoutManager->initControlsFor($contentStream);
  }

  protected function initFormPanel(\Psc\CMS\EntityFormPanel $panel) {
    $panel->setPanelButtons(
      array('preview','save','reload','save-close')
    );
    
    return parent::initFormPanel($panel);
  }
  
  public function getUploadService() {
    return new UploadService(
      '/cms/uploads',
      '/cms/uploads'
    );
  }
  
  protected function getContentStreamConverter() {
    return $this->container->getContentStreamConverter();
  }
  
  protected function getBaseUrl() {
    // lets get dirty
    return $this->dc->getModule()->getProject()->getBaseUrl();
  }
}
