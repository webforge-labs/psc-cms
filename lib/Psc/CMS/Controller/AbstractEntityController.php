<?php

namespace Psc\CMS\Controller;

use Psc\Doctrine\EntityRepository;
use Psc\CMS\Entity;
use Psc\CMS\EntityMeta;
use Psc\CMS\EntityGridPanel;
use Psc\Data\SetMeta;
use Psc\Doctrine\EntityNotFoundException;

use stdClass AS FormData;
use Psc\Code\Generate\GClass;
use Psc\Code\Code;

use Psc\Doctrine\DCPackage;
use Psc\CMS\EntityViewPackage;
use Psc\Form\ValidationPackage;
use Psc\Net\ServiceErrorPackage;

use Psc\Code\Event\CallbackSubscriber;
use Psc\CMS\ComponentMapper;
use Psc\CMS\ComponentsCreater;
use Psc\CMS\Component;
use Psc\Code\Event\Event;
use Psc\CMS\AjaxMeta;

use Psc\Data\ExportWrapper;
use Psc\CMS\EntityExporter;
use Psc\Data\ArrayExportCollection;

use Psc\CMS\Service\MetadataGenerator;

use Psc\UI\PanelButtons;

/**
 * @TODO wie werden hier "custom"-API Funktionen im Service registriert?
 * @TODO verhalten des GUIS wenn sich entities ändern (besnders wenn man den primärschlüssel ändert, geht ja alles kaputt)
 * @TODO patch
 */
abstract class AbstractEntityController implements TransactionalController, \Psc\CMS\ComponentsCreaterListener, ResponseMetadataController, \Psc\Code\Info {
  
  /**
   * @var Psc\Doctrine\EntityRepository
   */
  protected $repository;
  
  /**
   * @return string FQN des Entities
   */
  abstract public function getEntityName();
  
  /**
   * Ein Package für EntityManager / Module und alles zu Doctrine
   * @var DCPackage
   */
  protected $dc;
  
  /**
   * Ein Package für EntityFormPanel und EntityForm usw
   * 
   * @var EntityViewPackage
   */
  protected $ev;
  
  /**
   * Ein Package für das Validieren von Input und anderem
   * 
   * @var ValidationPackage
   */
  protected $v;
  
  /**
   * EIn Package für Service Errors
   *
   * dies benutzen wenn man keinen Plan hat welche HTTP-Nummer die richtige ist!
   * @var Psc\Net\ServiceErrorPackage
   */
  protected $err;
  
  /**
   * @var array schlüssel: name der Subresource aus getEntity() wert: name der Funktion die aufgerufen wird (erster parameter ist das entity)
   */
  protected $customActions = array();
  
  /**
   * Die Properties, die nicht im FormPanel angezeigt werden sollen
   *
   */
  protected $blackListProperties = array('id');
  protected $contextBlackListProperties = array('grid'=>array('id'));
  
  /**
   * Die Properties, die im ComponentsValidator auf "optional" gesetzt werden
   */
  protected $optionalProperties = array();
  
  /**
   * Order der Properties für den FormPanel
   * 
   * @var string[] Namen der Properties
   */
  protected $propertiesOrder;
  
  /**
   * @var Psc\CMS\MetadataGenerator
   */
  protected $metadata;
  
  /**
   * @var string
   */
  protected $defaultRevision = 'default';
  
  public function __construct(DCPackage $dc = NULL, EntityViewPackage $ev = NULL, ValidationPackage $v = NULL, ServiceErrorPackage $err = NULL) {
    $this->dc = $dc ?: new DCPackage();
    $this->ev = $ev ?: new EntityViewPackage();
    $this->v = $v ?: new ValidationPackage();
    $this->err = $err ?: new ServiceErrorPackage($this);
    $this->setUp();
  }
  
  protected function setUp() {
    $this->repository = $this->dc->getEntityManager()->getRepository($this->getEntityName());
  }

  /**
   * @return Entity|EntityFormPanel
   * @controller-api
   */
  public function getEntity($identifier, $subResource = NULL, $query = NULL) {
    return $this->getEntityInRevision($identifier, $this->defaultRevision, $subResource, $query);
  }
    
  /**
   * Returns a revision from the entity with the $parentIdentifier
   *
   * if no revision or identifier is found an Resource Not Found HTTP Error is raised
   * @return Entity
   */
  public function getEntityInRevision($parentIdentifier, $revision, $subResource = NULL, $query = NULL) {
    try {
      $entity = $this->hydrateEntityInRevision($parentIdentifier, $revision);
    } catch (EntityNotFoundException $e) {
      throw $this->err->resourceNotFound(__FUNCTION__, 'entity', array('identifier'=>$parentIdentifier), $e);
    } // was passiert wenn mehrere items gefunden werden? (bis jetzt 500)
    
    if ($subResource === NULL) {
      return $entity;
    } elseif ($subResource === 'form') {
      return $this->getEntityFormular($entity);
    } elseif (array_key_exists($subResource, $this->customActions)) {
      return $this->callCustomAction($this->customActions[$subResource], array($entity, $query, $revision));
    } else {
      throw $this->err->invalidArgument(__FUNCTION__, 'subResource', $subResource);
    }
  }
  
  /**
   * Returns the $revision of the entity with the $parentIdentifier
   *
   * notice that the returned Entity may not have the same identifier as $parentIdentifier
   * @return Entity
   */
  protected function hydrateEntityInRevision($parentIdentifier, $revision) {
    $identifier = $this->v->validateIdentifier($parentIdentifier, $this->getEntityMeta());
    
    if ($revision === $this->defaultRevision) {
      return $this->repository->hydrate($parentIdentifier);
    } else {
      throw EntityNotFoundException::criteria(compact('parentIdentifier', 'revision'));
    }
  }
    
  /**
   * Returns the entity with the $identifier
   * 
   * @return Entity in default Revision
   */
  protected function hydrateEntity($identifier) {
    return $this->hydrateEntityInRevision($identifier, $this->defaultRevision);
  }
  
  /**
   * Returns an specific Entity
   * 
   * @return Entity
   */
  protected function hydrate($entityName, $identifier) {
    return $this->dc->getRepository($entityName)->hydrate($identifier);
  }
  
  /**
   *
   * @controller-api
   * @TODO orderby?
   */
  public function getEntities(Array $query = array(), $subResource = NULL) {
    if ($subResource === 'search') {
      return $this->getEntitySearchPanel($this->getEntityMeta(), $query);

    } elseif ($subResource !== NULL && array_key_exists($subResource, $this->customActions)) {
      return $this->callCustomAction($this->customActions[$subResource], array($query));
    }

    if (array_key_exists('autocomplete',$query) && $query['autocomplete'] === 'true') {
      $entityMeta = $this->getEntityMeta();
      
      $fields = array_keys($entityMeta->getAutoCompleteFields());
      $maxResults = (isset($query['maxResults'])) ? max(1,(int) $query['maxResults']) : NULL;
      
      $entities = $this->doAutoCompleteEntities($fields,
                                                $query['search'],
                                                array_key_exists('filters', $query) ? (array) $query['filters'] : array(),
                                                $maxResults
                                               );
  
      $exporter = new \Psc\CMS\Item\Exporter();
      $export = array();
      foreach ($entities as $entity) {
        // @TODO es wäre schön hier spezifizieren zu können welche interfaces exportiert werden müssen
        // dies muss vom request her passieren
        
        $export[] = $exporter->ComboDropBoxable($entityMeta->getAdapter($entity)->getComboDropBoxable());
        //$export[] = $exporter->AutoCompletable($entityMeta->getAdapter($entity)->getAutoCompletable());
        
        //$export[] = $exporter->merge($entityMeta->getAdapter($entity),
        //                           array('AutoCompletable','Identifyable','TabOpenable')
        //                           );
      }
      
      if (isset($maxResults) && count($export) === $maxResults) { // exceeds
        $this->metadata = new MetadataGenerator();
        $this->metadata->autoCompleteMaxResultsHit($maxResults);
      }
      
      return $export;
    } else {
      $entities = $this->findEntitiesFor($query, $this->getDefaultSort(), $subResource);
    }
    
    if ($subResource === NULL) {
      return $entities;
    } elseif ($subResource === 'grid') {
      return $this->getEntityGrid($this->dc->getEntityMeta($this->getEntityName()), $entities);
    } else {
      throw $this->err->invalidArgument(__FUNCTION__, 'subResource', $subResource);
    }
  }
  
  protected function findEntitiesFor(Array $query, Array $sort, $subResource) {
    return $this->repository->findBy($query, $sort);
  }
  
  protected function doAutoCompleteEntities(Array $fields, $term, Array $filters, $maxResults) {
    return $this->repository->autoCompleteTerm($fields, $term, $filters, $maxResults);
  }

  /**
   * Saves the Entity default Revision
   * 
   * @controller-api
   * @return Entity
   */
  public function saveEntity($identifier, FormData $requestData, $subResource = NULL) {
    $revision = $this->defaultRevision;
    $entity = $this->getEntity($identifier);
    
    if ($subResource !== NULL && array_key_exists($subResource, $this->customActions)) {
      return $this->callCustomAction($this->customActions[$subResource], array($entity, $requestData, $revision));
    }
    
    return $this->updateEntityRevision($entity, $revision, $requestData, $subResource);
  }

  /**
   * Saves an Entity ($parentIdentifier) as a new Entity in a specific $evision
   *
   * notice: think of a save as... - button
   * this does NOT use getEntityInRevision to retrieve the revision-entity. it uses hydrateEntityInRevision
   * a new Revision is created (if it does not exist in db) with createNewRevisionFrom()
   *
   * @controller-api
   * @return Entity
   */
  public function saveEntityAsRevision($parentIdentifier, $revision, FormData $requestData, $subResource = NULL) {
    try {
      $entity = $this->hydrateEntityInRevision($parentIdentifier, $revision);
    } catch (EntityNotFoundException $e) {
      $parentEntity = $this->getEntity($parentIdentifier);
      
      $entity = $this->createNewRevisionFrom($parentEntity, $revision);
    }

    if ($subResource !== NULL && array_key_exists($subResource, $this->customActions)) {
      return $this->callCustomAction($this->customActions[$subResource], array($entity, $requestData, $revision));
    }
    
    return $this->updateEntityRevision($entity, $revision, $requestData, $subResource);
  }

  /**
   * Saves the specific Entity with the $requestData in $revision
   *
   * this does not create new revisions of the entity, it just stores the current with the requestdata
   * @return Entity
   */
  protected function updateEntityRevision(Entity $entity, $revision, FormData $requestData) {
    $this->setRevisionMetadata($revision);
    
    $this->beginTransaction();
    
    // validiert und processed die daten aus requestData und setzt diese im Entity
    $this->processEntityFormRequest($entity, $requestData, $revision);

    $this->repository->save($entity);
    
    $this->commitTransaction();
    
    $this->setEntityResponseMetadata($entity);
    
    return $entity;
  }
  
  protected function processEntityFormRequest(Entity $entity, FormData $requestData, $revision) {
    /* wir erstellen ein Meta-Set mit den Post-Daten
    
     das problem ist, wenn wir hier z.b. einen int aus dem Formular bekommen, ist dies ein string
     wir brauchen also eine instanz, die uns die formulardaten in die echten Typen umwandelt
     dies würden eigentlich die formValidatorRules machen
     diese wollten wir aber eigentlich nur zum validieren benutzen
    
     ich glaube wir brauchen 2 validation rules
      eine für das set (php-type schon richtig und normalisiert)
      und eine für formular-daten (kommt immer als string / array von strings, oder json)
      
      zuerst bauen wir jetzt aber erstmal einen automatischen FormValidator der so wie früher funktioniert
    */
    $panel = $this->getEntityFormPanel($entity);
    $panel->html(); // damit open gemacht wird und alle controlFields gesetzt werden, sowie die componenten erzeugt werden
    
    $requestData = $this->filterRequestData($requestData);

    $validator = $this->v->createComponentsValidator($requestData, $panel, $this->dc);
    $this->init(array('v.componentsValidator'));
    
    $validator->validateSet();
    
    // hier geht $data dann in den processor
    $processor = $this->createEntityProcessor($entity);
    $this->initProcessor($processor);
    
    // @TODO try catch
    // @TODO einzelne Componenten dürfen entscheiden ob ihre value im Entity gesetzt wird oder nicht
    $processor->processSet($validator->getSet());

    return $this;
  }

  /**
   * Inserts a new Entity in default Revision
   * 
   * @controller-api
   * @return Psc\CMS\Entity
   */
  public function insertEntity(FormData $requestData, $subResource = NULL) {
    return $this->insertEntityRevision($this->defaultRevision, $requestData, $subResource);
  }
  
  /**
   * Inserts a new Entity in specific Revision
   *
   * @controller-api
   */
  public function insertEntityRevision($revision, FormData $requestData, $subResource = NULL) {
    $entity = $this->createEmptyEntity($revision);
    
    if ($subResource !== NULL && array_key_exists($subResource, $this->customActions)) {
      return $this->callCustomAction($this->customActions[$subResource], array($entity, $requestData, $revision));
    }
    
    $this->updateEntityRevision($entity, $revision, $requestData);
    $this->setOpenTabMetadata($entity);
    
    return $entity;
  }

  /**
   * Returns a new $revision based on $parentEntity
   *
   */
  protected function createNewRevisionFrom(Entity $parentEntity, $revision) {
    // default behaviour is dumb
    return $this->createEmptyEntity($revision);
  }

  protected function setEntityResponseMetadata(Entity $entity) {
    if (!$this->metadata) $this->metadata = new MetadataGenerator();
    
    if (is_array($links = $this->getLinkRelationsForEntity($entity))) {
      $this->metadata->entity(
        $entity,
        $links
      );
    }
  }
  
  /**
   * Returns a set of (REST)-links as meta-information for the entity
   *
   * a link is a \Psc\Net\Service\LinkRelation
   * @return LinkRelation[]
   */
  protected function getLinkRelationsForEntity(Entity $entity) {
    return array();
  }
  

  protected function setRevisionMetadata($revision) {
    if (!$this->metadata) $this->metadata = new MetadataGenerator();
    
    $this->metadata->revision(
      $revision
    );
  }

  protected function setOpenTabMetadata($entity) {
    if (!isset($this->metadata)) {
      $this->metadata = new MetadataGenerator();
    }
    
    $this->metadata->openTab($this->getEntityMeta()->getAdapter($entity)->getTabOpenable());
  }

  
  protected function filterRequestData(FormData $requestData) {
    return $this->v->filterjqxWidgetsFromRequestData($requestData);
  }

    /**
   * @controller-api
   */
  public function patchEntity($identifier, FormData $fields) {
    $this->beginTransaction();
    
    $entity = $this->getEntity($identifier);
    
    $panel = $this->getEntityFormPanel($entity);
    $panel->html();
    
    $components = array();
    foreach ($fields as $field => $value) {
      $components[$field] = $panel->getComponent($field);
    }
    
    $validator = $this->v->createComponentsValidator($fields, $panel, $this->dc, $components);
    $this->init(array('v.componentsValidator'));
    
    $validator->validateSet();

    // hier geht $data dann in den processor
    $processor = $this->createEntityProcessor($entity);
    $this->initProcessor($processor);
    $processor->processSet($validator->getSet());
    
    $this->repository->save($entity);
    $this->commitTransaction();
    
    return $entity;
  }

  /**
   * @controller-api
   */
  public function deleteEntity($identifier, $subResource = NULL) {
    $this->beginTransaction();

    $entity = $this->getEntity($identifier);
    $oldIdentifier = $entity->getIdentifier(); // casted zur richtigen value
    
    $this->onDelete($entity);
    
    $this->commitTransaction();
    
    // @TODO tabs meta: tab should be closed! RightContent-Removed usw
    
    $entity->setIdentifier($oldIdentifier);
    // beim flush setzt doctrine den identifier auf null, da hat es ja recht
    // wir wollen aber das entity noch exportieren um das "entity was deleted" event mit richtigem "alten" identifier senden zu können
    
    return $entity;
  }

  protected function onDelete(Entity $entity) {
    $this->repository->delete($entity); // delete macht remove + flush zusammen
  }

  /**
   * Gibt das EntityFormular (ein dekorierter EntityFormPanel zurück)
   * 
   */
  public function getEntityFormular(Entity $entity) {
    return $this->getEntityFormPanel($entity);
  }
  
  public function getEntityFormPanel(Entity $entity, $panelLabel = NULL, \Psc\CMS\RequestMeta $requestMeta = NULL) {
    $this->init(array('ev.componentMapper', 'ev.labeler'));
    $entityMeta = $this->getEntityMeta();

    $panel = $this->createFormPanel($entity, $panelLabel);

    /* Übernehme URL + Methode für den Panel aus EntityMeta*/
    if ($entity->isNew()) {
      $panel->setRequestMeta($requestMeta ?: $entityMeta->getNewRequestMeta());
      $panel->setPanelButtons(new PanelButtons(array('insert','reset','insert-open')));
    } else {
      $panel->setRequestMeta($requestMeta ?: $entityMeta->getSaveRequestMeta($entity));
      $panel->setPanelButtons(new PanelButtons(array('save','reload','save-close')));
    }
    
    // custom init
    $this->init(array('ev.formPanel'));
    
    // erstellen
    $panel->createComponents();
    
    // sortieren
    if (is_array($this->propertiesOrder)) {
      $panel->getEntityForm()->sortComponents($this->propertiesOrder);
    }
    
    return $panel;
  }

  /**
   * Der Grid ist eine pflegbare Liste in dem die Items als Buttons dargestellt werden
   *
   * @controller-api
   */
  public function getEntityGrid(EntityMeta $entityMeta, $entities) {
    $this->init(array('ev.componentMapper', 'ev.labeler'));
    
    $panel = $this->createGridPanel($entityMeta);
    $this->init(array('ev.gridPanel'));
    
    // entities zuweisen
    $panel->addEntities($entities);
    
    return $panel;
  }

  /**
   * Speichert die Reihenfolge (wenn vorhanden) für die Entities des Controllers ab
   *
   * dies ist die Hilfs-Implementierung für SortingController
   * die Sortierung wird von 1-basierend von unten nach oben durchnummeriert
   *
   * @return array $identifier => $savedSortInteger
   */
  public function saveSort(Array $sortMap) {
    $this->beginTransaction();
    $field = $this->getSortField();
    
    $sorts = array();
    foreach ($sortMap as $sort => $identifier) {
      $sorts[$identifier] = $sort+1;
      $this->repository->persist(
        $this->getEntity($identifier)->callSetter($field, $sort+1)
      );
    }
    
    $this->dc->getEntityManager()->flush();
    $this->commitTransaction();
    
    return $sorts;
  }

  /**
   * Der Grid ist eine pflegbare Liste in dem die Items als Buttons dargestellt werden
   *
   * @param array $query noch nicht benutzt.
   * @controller-api
   */
  public function getEntitySearchPanel(EntityMeta $entityMeta, Array $query) {
    $panel = $this->ev->createSearchPanel($entityMeta, $query);
    $this->init(array('ev.searchPanel'));
    
    return $panel;
  }
  
  /**
   * @controller-api
   */
  public function getNewEntityFormular() {
    $entity = $this->createEmptyEntity($this->defaultRevision);
    return $this->getEntityFormular($entity);
  }
  
  
  public function getResponseMetadata() {
    return $this->metadata;
  }
  
  /**
   * Erstellt ein leeres Entity
   *
   * kann abgeleitet werden, falls der constructor von {$this->getEntityName()} Parameter braucht
   */
  public function createEmptyEntity($revision = NULL) {
    
    // @TODO check ob entity einen constructor hat, der parameter braucht?
    $c = $this->getEntityName();
    
    try {
      $gClass = new GClass($c);
      
      // wir rufen hier nicht den constructor auf
      return $gClass->newInstance(array(), GClass::WITHOUT_CONSTRUCTOR);
      
    } catch (\ErrorException $e) {
      if (mb_strpos($e->getMessage(), 'Missing argument') !== FALSE) {
        throw new \Psc\Exception(sprintf("Kann kein leeres Entity '%s' mit leerem Constructor erstellen. createEmptyEntity() kann im Controller abgeleitet werden, um das Problem zu beheben",$c), 0, $e);
      } else {
        throw $e;
      }
    }
  }
  
  /**
   * 
   * @return EntityFormPanel
   */
  public function createFormPanel(Entity $entity, $panelLabel = NULL) {
    $entityMeta = $this->getEntityMeta();
    
    $panel = $this->ev->createFormPanel(
      $panelLabel ?: ($entity->isNew() ? $entityMeta->getNewLabel() : $entityMeta->getEditLabel()),
      $this->ev->createEntityForm($entity, $entity->isNew() ? $entityMeta->getNewRequestMeta($entity) : $entityMeta->getSaveRequestMeta($entity))
    );
    return $panel;
  }
  
  /**
   * @return EntityGridPanel
   */
  public function createGridPanel(EntityMeta $entityMeta, $panelLabel = NULL) {
    $panel = $this->ev->createGridPanel(
      $entityMeta,
      $panelLabel
    );
    
    return $panel;
  }

  protected function createEntityProcessor(Entity $entity) {
    return new \Psc\Doctrine\Processor($entity, $this->dc->getEntityManager(), $this->dc);
  }

  /**
   * @param $type normal|hydration
   * @return Psc\Doctrine\EntityCollectionSynchronizer
   */
  protected function getSynchronizerFor($collectionPropertyName, $type = 'normal') {
    Code::value($type, 'normal', 'hydration');
    
    if ($type === 'hydration') {
      $c = 'Psc\Doctrine\PersistentCollectionSynchronizer';
    } else {
      $c = 'Psc\Doctrine\CollectionSynchronizer';
    }
    
    return $c::createFor($this->getEntityName(), $collectionPropertyName, $this->dc);
  }

  protected function callCustomAction($callback, Array $params = array()) {
    if (is_string($callback)) {
      $callback = array($this, $callback);
    }
    
    return call_user_func_array($callback, $params);
  }

  /**
   * @chainable
   */
  public function setRepository(EntityRepository $repository) {
    $this->repository = $repository;
    return $this;
  }


  protected function init(Array $dependencies) {
    foreach ($dependencies as $dependency) {
      list($package, $property) = explode('.',$dependency,2);
      $init = 'init'.ucfirst($property);
      $get = 'get'.ucfirst($property);
      $this->$init( // Damit es overloadbar ist
        $this->$package->$get()
      );
    }
    return $this;
  }

  protected function initComponentMapper(\Psc\CMS\ComponentMapper $mapper) {}
  protected function initLabeler(\Psc\CMS\Labeler $labeler) {}
  
  /**
   * Fügt per Default alle Whitelist Properties der Tabelle hinzu
   *
   */
  protected function initGridPanel(\Psc\CMS\EntityGridPanel $panel) {
    if ($this instanceof SortingController) {
      $panel->setSortable(TRUE);
      $panel->setSortableName($this->getSortField());
    }
    
    $panel->addControlColumn();
    $panel->addControlButtons();
    
    foreach ($this->getWhiteListProperties($panel->getEntityMeta(), 'grid') as $property) {
      $panel->addProperty($property, $panel->getEntityMeta()->getTCIColumn() === $property ? EntityGridPanel::TCI_BUTTON : NULL);
    }
  }
  
  protected function initSearchPanel(\Psc\CMS\EntitySearchPanel $panel) {}
  
  protected function initFormPanel(\Psc\CMS\EntityFormPanel $panel) {
    $entity = $panel->getEntity();
    
    $panel->setWhiteListProperties($this->getWhiteListProperties($entity, 'form'));
    
    // das wichtigste: wir müssen natürlich subscriben damit die onComponentCreated*() Funktionen auch aufgerufen werden
    $panel->subscribe($this);
    
    $panel->createRightAccordion($this->getEntityMeta());
    return $panel;
  }

  /**
   * Fügt Metadaten zu Relations hinzu, die z. B. die  FormComboDropBox braucht
   *
   */
  protected function onEntityRelationComponentCreated(Component $component, ComponentsCreater $creater, Event $event, EntityMeta $relationEntityMeta) {
    
    if ($component instanceof \Psc\UI\Component\ComboDropBox) {
      // in der ComboDropBox werden ja fremde Entities mit Ajax geladen, d.h. wir müssen hier relationEntityMeta übergebe
      $component->dpi($relationEntityMeta, $this->dc);
    } elseif ($component instanceof \Psc\UI\Component\ComboBox) {
      $component->dpi($relationEntityMeta, $this->dc);
    } elseif ($component instanceof \Psc\UI\Component\SingleImage) {
      $component->dpi($relationEntityMeta, $this->dc);
    }
  }

  protected function initComponentsValidator(\Psc\Form\ComponentsValidator $componentsValidator) {
    foreach ($this->optionalProperties as $property) {
      $componentsValidator->setOptional($property);
    }
  }
  
  protected function initProcessor(\Psc\Doctrine\Processor $processor) {}

  /* Interface TransactionalController */
  
  /**
   * @return bool
   */
  public function hasActiveTransaction() {
    return $this->dc->hasActiveTransaction();
  }

  public function beginTransaction() {
    return $this->dc->beginTransaction();
  }
  
  public function commitTransaction() {
    return $this->dc->commitTransaction();
  }
  
  public function rollbackTransaction() {
    return $this->dc->rollbackTransaction();
  }
  
  /* Interface ComponentsCreaterListener*/
  public function onComponentCreated(Component $component, ComponentsCreater $creater, Event $event) {
    $entityMeta = $this->getEntityMeta();
    $propertyMeta = $entityMeta->getPropertyMeta($event->getData()->name);
    
    if (($hint = $propertyMeta->getHint()) !== NULL) {
      $event->getData()->hint = $hint;
      $component->setHint($hint);
    }
    
    if ($propertyMeta->isRelation()) {
      $relationEntityMeta = $this->getEntityMeta($propertyMeta->getRelationEntityClass()->getFQN());
      
      $this->onEntityRelationComponentCreated($component, $creater, $event, $relationEntityMeta);
    }
    
    // Rufe on{$Property}ComponentCreated  auf, wenn es die Methode gibt
    $method = 'on'.$propertyMeta->getName().'ComponentCreated';
    if (method_exists($this, $method)) {
      $this->$method($component, $creater, $event);
    }
  }
  
  /**
   * @param array $optionalProperties
   * @chainable
   */
  public function setOptionalProperties(Array $optionalProperties) {
    $this->optionalProperties = $optionalProperties;
    return $this;
  }
  
  public function addOptionalProperty($name) {
    $this->optionalProperties[] = $name;
    return $this;
  }
  
  /**
   * Gibt die DefaultSortierung für Entities z.b. bei getEntities zurück
   *
   * dies ist dann z.b. die Reihenfolge für den GridTable oder ein normales API-Query (Nicht autocomplete)
   */
  public function getDefaultSort() {
    if ($this instanceof SortingController) {
      return array($this->getSortField()=>'ASC');
    } else {
      return array($this->getEntityMeta()->getIdentifier()->getName()=>'ASC');
    }
  }
  
  /**
   * @return array von keys aus dem metaSet des Entities
   */
  protected function getWhiteListProperties($entityOrEntityMeta, $context = 'form') {
    return array_diff($entityOrEntityMeta->getSetMeta()->getKeys(), $this->getBlackListProperties($context));
  }
  
  
  /**
   * Gibt die EntityMeta zu diesem (einem bestimmten) Entity zurück
   * 
   * @param string|NULL $entityClass wird die klasse nicht übergeben wird $this->getEntityName() genommen
   * @return Psc\CMS\EntityMetaData
   */
  public function getEntityMeta($entityClass = NULL) {
    return $this->dc->getEntityMeta($entityClass ?: $this->getEntityname());
  }

  /**
   * @return array
   */
  public function addCustomAction($subResource, $callback) {
    $this->customActions[$subResource] = $callback;
    return $this;
  }

  /**
   * @return array
   */
  public function getOptionalProperties() {
    return $this->optionalProperties;
  }
  
  public function getDoctrinePackage() {
    return $this->dc;
  }
  
  public function getBlackListProperties($context = 'form') {
    if ($context === 'form' || !array_key_exists($context, $this->contextBlackListProperties)) {
      return $this->blackListProperties;
    } else {
      return $this->contextBlackListProperties[$context];
    }
  }
  
  public function addBlackListProperties(Array $properties, $context = 'form') {
    if (array_key_exists($context, $this->contextBlackListProperties)) {
      $this->contextBlackListProperties[$context] = array_merge($this->contextBlackListProperties[$context], $properties);
    } else {
      $this->blackListProperties = array_merge($this->blackListProperties, $properties);
    }
  }
  
  public function addBlackListProperty($propertyName, $context = 'form') {
    if (array_key_exists($context, $this->contextBlackListProperties)) {
      $this->contextBlackListProperties[$context][] = $propertyName;
    } else {
      $this->blackListProperties[] = $propertyName;
    }
    return $this;
  }
  
  public function removeBlackListProperty($propertyName, $context = 'form') {
    if (array_key_exists($context, $this->contextBlackListProperties)) {
      \Webforge\Common\ArrayUtil::remove($this->contextBlackListProperties[$context], $propertyName);
    } else {
      \Webforge\Common\ArrayUtil::remove($this->blackListProperties[$context], $propertyName);
    }
    return $this;
  }
  
  public function getVarInfo() {
    return 'Entity-Controller: '.$this->getEntityName();
  }
  
  public function setPropertiesOrder(Array $names) {
    $this->propertiesOrder = $names;
    return $this;
  }
}
?>