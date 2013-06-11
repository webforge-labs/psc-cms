<?php

namespace Psc\CMS;

use Doctrine\ORM\Mapping\ClassMetadata;
use Psc\Code\Code;

/**
 * Eine Meta-Klasse für erweiterte Labels oder Informationen zum Entity
 *
 *  - erstellt TabsContentItems für verschiedene Contexte
 *  - verwaltet Labels
 *  - verwaltet RequestMeta für alle Entities
 *  - alle weiteren Einstellungen für das Entity
 *
 * Dies ist die einzige Klasse die URLs für den EntityService erzeugt!
 * die URLs werden in Form von RequestMeta - Objekten gekapselt (damit man auch POSTen kann)
 *
 * @TODO getDefaultRequestMeta dings
 */
class EntityMeta extends \Psc\SimpleObject {
  
  // context z.b. für getLabelFor
  const CONTEXT_DEFAULT = 'default';
  const CONTEXT_ASSOC_LIST = 'assocList';
  const CONTEXT_LINK = 'link';
  const CONTEXT_RIGHT_CONTENT = 'rightContent';
  const CONTEXT_TAB = 'tab';
  const CONTEXT_BUTTON = 'button';
  const CONTEXT_FULL = 'full';
  const CONTEXT_AUTOCOMPLETE = 'autocomplete';
  const CONTEXT_GRID = 'grid'; // darstellung eines entities im grid nicht das grid label
  const CONTEXT_SEARCHPANEL = 'searchPanel';
  const CONTEXT_ACTION = 'action';
  const CONTEXT_DELETE = 'delete';
  
  /**
   *
   * z. B. "eines Sprechers"
   * @var string
   */
  protected $genitiv;
  
  /**
   *
   * z. B. Neuen Sprecher erstellen
   * @var string
   */
  protected $newLabel = 'Neues Entity erstellen';
  protected $editLabel = '%s bearbeiten';
  protected $deleteLabel = '%s löschen';
  protected $gridLabel = NULL;
  protected $searchLabel;
  
  protected $autoCompleteFields = array('id'=>'Nummer');
  protected $autoCompleteDelay = 300;
  protected $autoCompleteMinLength = 2;
  protected $autoCompleteHeadline = NULL;
  
  protected $labels = array();
  
  protected $legacyType;
  
  protected $class;
  
  /**
   * Cache für SetMeta aus dem Entity
   */
  protected $setMeta;
  
  /**
   * ClassMetadata von Doctrine aus dem DefaultEntityManager
   *
   * @var Doctrine\ORM\Mapping\ClassMetadata
   */
  protected $classMetadata;
  
  /**
   * Cache für EntityPropertyMeta der Properties
   */
  protected $properties = array();
  
  protected $newFormRequestMeta;
  protected $newRequestMeta;
  protected $deleteRequestMeta;
  protected $saveRequestMeta;
  protected $searchRequestMeta;
  protected $searchPanelRequestMeta;
  protected $gridRequestMeta;
  protected $autoCompleteRequestMeta;
  
  protected $actionsRequestMeta = array();
  
  protected $urlPrefixParts = array('entities');
  
  public function __construct($class, ClassMetadata $classMetadata, $labels) {
    $this->class = $class;
    
    if (is_array($labels)) {
      $this->labels = $labels;
    } elseif (is_string($labels)) {
      $this->labels[self::CONTEXT_DEFAULT] = $labels;
    } else {
      throw new \InvalidArgumentException('Labels muss ein String oder ein Label-Array sein');
    }
    
    $this->classMetadata = $classMetadata;
  }
  
  public function getSetMeta() {
    if (!isset($this->setMeta)) {
      $class = $this->getGClass()->getFQN();
      if (!$this->getGClass()->exists()) {
        throw new \Psc\Exception(sprintf("Entity Klasse von Meta: '%s' existiert nicht.", $class));
      }
      
      $this->setMeta = $class::getSetMeta();
    }
    
    return $this->setMeta;
  }
  
  /**
   * @return Psc\CMS\Item\Adapter|Psc\CMS\Item\MetaAdapter
   */
  public function getAdapter($entityOrContext = NULL, $context = Item\MetaAdapter::CONTEXT_DEFAULT, $action = NULL) {
    if ($entityOrContext instanceof Entity) {
      $adapter = new Item\Adapter($entityOrContext, $this, $context);
      if ($context === self::CONTEXT_ACTION) {
        $adapter->setRequestMetaAction($action);
      }
      return $adapter;
    } elseif (!empty($entityOrContext)) {
      return new Item\MetaAdapter($this, $entityOrContext);
    } else {
      return new Item\MetaAdapter($this, $context);
    }
  }
  
  public function getLabel($context = self::CONTEXT_DEFAULT) {
    return array_key_exists($context, $this->labels) ? $this->labels[$context] : $this->labels['default'];    
  }
  
  public function setLabel($label, $context = self::CONTEXT_DEFAULT) {
    $this->labels[$context] = $label;
    return $this;
  }
  
  public function hasProperty($propertyName) {
    return $this->getSetMeta()->hasField($propertyName);
  }
  
  /**
   * @return Psc\CMS\EntityPropertyMeta
   */
  public function getPropertyMeta($name, Labeler $labeler = NULL) {
    if (!array_key_exists($name, $this->properties)) {
      if (!isset($labeler)) $labeler = new \Psc\CMS\Labeler();
      
      try {
        $type = $this->getSetMeta()->getFieldType($name); // schmeiss exception wenns das property nicht gibt
      } catch (\Psc\Data\FieldNotDefinedException $e) {
        throw \Psc\Doctrine\FieldNotDefinedException::entityField($this->getClass(), $name);
      }
      
      $this->properties[$name] = $meta = new EntityPropertyMeta($name, $type, $labeler->getLabel($name));

      if ($this->classMetadata->isIdentifier($name) && $this->getClassMetadata()->usesIdGenerator()) {
        $meta->setAutogeneratedValue(TRUE);
      }
    } elseif (isset($labeler)) {
      // update
      $this->properties[$name]->setLabel($labeler->getLabel($name));
    }
    
    return $this->properties[$name];
  }
  
  /**
   * Gibt das Property für eine Objekt-Fremd-Beziehung zurück
   *
   * dies ist z.B. bei der ManyToOne Seite möglich
   * @return Psc\CMS\EntityPropertyMeta
   */
  public function getForeignPropertyMeta(EntityMeta $foreignEntity) {
    throw new \Psc\Code\NotImplementedException();
    $foreignField = \Psc\Inflector::create()->propertyName($foreignEntity->getGClass()->getClassName());
    
    //var_dump($foreignEntity->getClassMetadata()->associationMappings);
    //var_dump($this->classMetadata->associationMappings);
    //var_dump($this->classMetadata->getFieldForColumn($foreignField));
    
    $mapping = $this->classMetadata->getAssociationMapping(
      
    );
    //var_dump($mapping);

    return $this->getPropertyMeta($mapping);
  }
  
  /**
   * Setzt die Hints für mehrere Properties
   *
   * (werden unter der Komponente angezeigt)
   * 
   * @param array $hints string $propertyName => string $propertyHint
   */
  public function setPropertiesHints(Array $hints) {
    foreach ($hints as $propertyName => $hint) {
      $this->getPropertyMeta($propertyName)
        ->setHint($hint);
    }
    return $this;
  }
  
  /**
   * @param string $genitiv
   * @chainable
   */
  public function setGenitiv($genitiv) {
    $this->genitiv = $genitiv;
    return $this;
  }

  /**
   * @return string
   */
  public function getGenitiv() {
    return $this->genitiv;
  }

  /**
   * @param array $autoCompleteFields
   * @chainable
   */
  public function setAutoCompleteFields(Array $autoCompleteFields) {
    $this->autoCompleteFields = $autoCompleteFields;
    return $this;
  }

  /**
   * @return array
   */
  public function getAutoCompleteFields() {
    return $this->autoCompleteFields;
  }
  
  /**
   * Der EntityName ist eine KurzForm der Klasse
   *
   * Dies ist für \tiptoi\Entities\Sound z. b. "sound"
   */
  public function getEntityName() {
    return Code::camelCaseToDash($this->getGClass()->getClassName());
  }
    
  /**
   * Der EntityName in Plural ist eine KurzForm der Klasse in plural
   *
   * Dies ist für \tiptoi\Entities\Sound z. b. "sounds"
   */
  public function getEntityNamePlural() {
    return \Psc\Inflector::plural($this->getEntityName());
  }
  
  public function getLegacyType() {
    if (!isset($this->legacyType)) {
      $this->legacyType = 'entities-'.$this->getEntityName();
    }
    return $this->legacyType;
  }

  /**
   * @param string $class
   * @chainable
   */
  public function setClass($class) {
    $this->class = $class;
    return $this;
  }

  /**
   * @return string der FQN
   */
  public function getClass() {
    return $this->class;
  }
  
  public function getGClass() {
    return new \Psc\Code\Generate\GClass($this->getClass());
  }


  /**
   * Gibt das Property der Spalte zurück die der Identifier des Entities ist
   * 
   * @return \Psc\CMS\EntityPropertyMeta
   */
  public function getIdentifier() {
    $identifiers = $this->getClassMetadata()->getIdentifierFieldNames();
    
    if (count($identifiers) > 1) {
      throw new \Psc\Code\NotImplementedException('Composite Identifiers werden nicht unterstützt: '.Code::varInfo($identifiers));
    }
    
    return $this->getPropertyMeta(array_pop($identifiers));
  }
  
  /**
   * Der Name eines Properties, welches bei einer Auflistung der Properties als TCI dargestellt werden soll
   *
   * z. B. in einem Grid wäre dieses Property dann ein Button mit dem Inhalt des Properties als Beschriftung
   * @return string
   */
  public function getTCIColumn() {
    if (!isset($this->tciColumn)) {
      return 'identifier';
    }
    return $this->tciColumn;
  }
  
  /**
   * @return string
   */
  public function getGridLabel() {
    return $this->gridLabel ?: $this->getLabel().' verwalten';
  }
  
  public function setGridLabel($label) {
    $this->gridLabel = $label;
    return $this;
  }

  /**
   * "Entity Suchen"
   * 
   * @return string
   */
  public function getSearchLabel() {
    return $this->searchLabel ?: $this->getLabel().' Suchen';
  }
  
  /**
   * @param string $label
   */
  public function setSearchLabel($label) {
    $this->searchLabel = $label;
    return $this;
  }
  
  public function setTCIColumn($columnName) {
    $this->tciColumn = $columnName;
    return $this;
  }

  /**
   * @param int $autoCompleteMinLength
   * @chainable
   */
  public function setAutoCompleteMinLength($autoCompleteMinLength) {
    $this->autoCompleteMinLength = $autoCompleteMinLength;
    return $this;
  }

  /**
   * @return int
   */
  public function getAutoCompleteMinLength() {
    return $this->autoCompleteMinLength;
  }

  /**
   * @param int $autoCompleteDelay
   * @chainable
   */
  public function setAutoCompleteDelay($autoCompleteDelay) {
    $this->autoCompleteDelay = $autoCompleteDelay;
    return $this;
  }

  /**
   * @return int
   */
  public function getAutoCompleteDelay() {
    return $this->autoCompleteDelay;
  }

  /**
   * @param string $autoCompleteHeadline
   * @chainable
   */
  public function setAutoCompleteHeadline($autoCompleteHeadline) {
    $this->autoCompleteHeadline = $autoCompleteHeadline;
    return $this;
  }

  /**
   * @return string
   */
  public function getAutoCompleteHeadline() {
    return $this->autoCompleteHeadline;
  }

  /**
   * @param string $legacyType
   * @chainable
   */
  public function setLegacyType($legacyType) {
    $this->legacyType = $legacyType;
    return $this;
  }
  
  /**
   * @param Doctrine\ORM\Mapping\ClassMetadata $classMetadata
   * @chainable
   */
  public function setClassMetadata(\Doctrine\ORM\Mapping\ClassMetadata $classMetadata) {
    $this->classMetadata = $classMetadata;
    return $this;
  }

  /**
   * @return Doctrine\ORM\Mapping\ClassMetadata
   */
  public function getClassMetadata() {
    return $this->classMetadata;
  }
  
  /**
   * @param string $newLabel
   * @chainable
   */
  public function setNewLabel($newLabel) {
    $this->newLabel = $newLabel;
    return $this;
  }

  /**
   * @return string
   */
  public function getNewLabel() {
    return $this->newLabel;
  }

  /**
   * @param string $newLabel
   * @chainable
   */
  public function setEditLabel($newLabel) {
    $this->editLabel = $newLabel;
    return $this;
  }

  /**
   * @return string
   */
  public function getEditLabel() {
    return sprintf($this->editLabel, $this->getLabel());
  }

  /**
   * @param string $newLabel
   * @chainable
   */
  public function setDeleteLabel($newLabel) {
    $this->deleteLabel = $newLabel;
    return $this;
  }

  /**
   * @return string
   */
  public function getDeleteLabel(Entity $entity = NULL) {
    // @TODO respekt entity
    
    return sprintf($this->deleteLabel, $this->getLabel());
  }
  
  /**
   * Neues Entity Erstellen (POST)
   * 
   * @param Psc\CMS\RequestMeta $NewRequestMeta
   * @chainable
   */
  public function getNewRequestMeta($clone = TRUE, $subResource = NULL) {
    if (!isset($this->newRequestMeta)) {
      $this->newRequestMeta = new RequestMeta(\Psc\Net\HTTP\Request::POST,
                                              $this->constructUrl(array($this->getEntityNamePlural()))
                                             );
    }
    if ($clone) {
      $meta = clone $this->newRequestMeta;
      if ($subResource != NULL) {
        $meta->appendUrl('/'.$subResource);
      }
      return $meta;
    }
    return $this->newRequestMeta;
  }

  /**
   * "Formular" Speichern (PUT)
   * 
   * @return Psc\CMS\RequestMeta
   */
  public function getSaveRequestMeta(Entity $entity = NULL, $subResource = NULL) {
    $idIsInt = ($type = $this->getIdentifier()->getType()) instanceof \Psc\Data\Type\IntegerType;
    
    // custom save request
    if ($subResource != NULL) {
      $requestMeta = new RequestMeta(\Psc\Net\HTTP\Request::PUT,
                             $this->constructUrl(array($this->getEntityName(), ($idIsInt ? '%d' : '%s'), $subResource)),
                             array($type)
                            );
      if (isset($entity)) {
        $requestMeta->setInput($entity->getIdentifier());
      }
      
      return $requestMeta;
    
    } else {
      if (!isset($this->saveRequestMeta)) {
        $this->saveRequestMeta = new RequestMeta(\Psc\Net\HTTP\Request::PUT,
                                               $this->constructUrl(array($this->getEntityName(), ($idIsInt ? '%d' : '%s'))),
                                               array($type)
                                              );
      }
      if (isset($entity)) {
        $meta = clone $this->saveRequestMeta;
        return $meta->setInput($entity->getIdentifier());
      }
    
      return $this->saveRequestMeta;
    }
  }
  
  /**
   * Formular anzeigen
   * 
   * @return Psc\CMS\RequestMeta
   */
  public function getFormRequestMeta(Entity $entity = NULL) {
    return $this->getActionRequestMeta('form', $entity);
  }
  
  public function getDefaultRequestMeta(Entity $entity = NULL) {
    if (isset($entity)) {
      // das Entity entscheidet selbst
      if ($entity instanceof DefaultRequestableEntity) {
        return $entity->getDefaultRequestMeta($this);
      } else {
        return $this->getFormRequestMeta($entity);
      }
    } else {
      return $this->getGridRequestMeta(TRUE);
    }
  }
 
  /**
   * Zeigt die Tabellen-Übersicht für ein Entity an
   * 
   * @return Psc\CMS\RequestMeta
   */
  public function getGridRequestMeta($clone = TRUE, $save = FALSE) {
    if ($save) {
      return new RequestMeta(\Psc\Net\HTTP\Request::PUT,
                             $this->constructUrl(array($this->getEntityNamePlural(), 'grid'))
                            );
    }
    
    if (!isset($this->gridRequestMeta)) {
      $this->gridRequestMeta = new RequestMeta(\Psc\Net\HTTP\Request::GET,
                                               $this->constructUrl(array($this->getEntityNamePlural(), 'grid'))
                                              );
    }
    
    if ($clone) {
      return clone $this->gridRequestMeta;
    } else {
      return $this->gridRequestMeta;
    }
  }


  /**
   * Gibt die Custom-Action zu einem Entity zurück
   *
   * also sowas wie entities/<entityName>/<identifier>/$action
   * @return requestMeta
   */
  public function getActionRequestMeta($action, Entity $entity = NULL, $method = \Psc\Net\HTTP\Request::GET) {
    if (!array_key_exists($action, $this->actionsRequestMeta)) {
      $idIsInt = ($identifierType = $this->getIdentifier()->getType()) instanceof \Psc\Data\Type\IntegerType;
      $this->actionsRequestMeta[$action] = new RequestMeta($method,
                                               $this->constructUrl(array($this->getEntityName(), ($idIsInt ? '%d' : '%s'), $action)),
                                               array($identifierType)
                                              );
    }
    
    if (isset($entity)) {
      $meta = clone $this->actionsRequestMeta[$action];
      return $meta->setInput($entity->getIdentifier());
    } else {
      return $this->actionsRequestMeta[$action];
    }
  }
  
  /**
   * Zeigt das "Entity erstellen" Formular an
   * 
   * @return Psc\CMS\RequestMeta
   */
  public function getNewFormRequestMeta($clone = TRUE) {
    if (!isset($this->newFormRequestMeta)) {
      $this->newFormRequestMeta = new RequestMeta(\Psc\Net\HTTP\Request::GET,
                                               $this->constructUrl(array($this->getEntityNamePlural(), 'form'))
                                              );
    }
    
    if ($clone) {
      return clone $this->newFormRequestMeta;
    } else {
      return $this->newFormRequestMeta;
    }
  }

  /**
   * Request zum Suchen von Entities und zur LowLevel Ausgabe
   * 
   * @return Psc\CMS\RequestMeta
   */
  public function getSearchRequestMeta(Array $query = NULL) {
    if (!isset($this->searchRequestMeta)) {
      $this->searchRequestMeta = new RequestMeta(\Psc\Net\HTTP\Request::GET,
                                               $this->constructUrl(array($this->getEntityNamePlural(), '%s')),
                                               array(RequestMeta::QUERY)
                                              );
    }
    
    if (isset($query)) {
      $meta = clone $this->searchRequestMeta;
      return $meta->setInput($query);
    }
    return $this->searchRequestMeta;
  }
  
  public function getSearchPanelRequestMeta($clone = TRUE) {
    if (!isset($this->searchPanelRequestMeta)) {
      $this->searchPanelRequestMeta = new RequestMeta(\Psc\Net\HTTP\Request::GET,
                                               $this->constructUrl(array($this->getEntityNamePlural(), 'search')),
                                               array()
                                              );
    }
    
    if ($clone) {
      return clone $this->searchPanelRequestMeta;
    }
    return $this->searchPanelRequestMeta;
  }

  /**
   * @return Psc\CMS\RequestMeta
   */
  public function getAutoCompleteRequestMeta(Array $query = NULL) {
    if (!isset($this->autoCompleteRequestMeta)) {
      $this->autoCompleteRequestMeta = new AutoCompleteRequestMeta(
                                        \Psc\Net\HTTP\Request::GET,
                                        $this->constructUrl(array($this->getEntityNamePlural())),
                                        array(),
                                        NULL,
                                        // weitere meta daten für auto Complete
                                        $this->getAutoCompleteMinLength(),
                                        $this->getAutoCompleteDelay()
                                      );
      $this->autoCompleteRequestMeta->setBody((object) array(
        'autocomplete'=>'true'
      ));
    }
    
    if (isset($query)) {
      $meta = clone $this->autoCompleteRequestMeta;
      return $meta->setInput($query);
    }
    return $this->autoCompleteRequestMeta;
  }

  /**
   * @return Psc\CMS\RequestMeta
   */
  public function getDeleteRequestMeta(Entity $entity = NULL) {
    if (!isset($this->deleteRequestMeta)) {
      $idIsInt = ($type = $this->getIdentifier()->getType()) instanceof \Psc\Data\Type\IntegerType;
      $this->deleteRequestMeta = new RequestMeta(\Psc\Net\HTTP\Request::DELETE,
                                               $this->constructUrl(array($this->getEntityName(), ($idIsInt ? '%d' : '%s'))),
                                               //'/entities/'.$this->getEntityName().'/'.($idIsInt ? '%d' : '%s'),
                                               array($type)
                                              );
    }
    
    if (isset($entity)) {
      $meta = clone $this->deleteRequestMeta;
      return $meta->setInput($entity->getIdentifier());
    }
    return $this->deleteRequestMeta;
  }
  
  protected function constructUrl(Array $parts) {
    $parts = array_merge((array) $this->urlPrefixParts,$parts);
    
    return '/'.implode('/', $parts);
  }
  
  /**
   * @param array $urlPrefixParts
   * @chainable
   */
  public function setUrlPrefixParts(array $urlPrefixParts) {
    $this->urlPrefixParts = $urlPrefixParts;
    return $this;
  }

  /**
   * @return array
   */
  public function getUrlPrefixParts() {
    return $this->urlPrefixParts;
  }
}
?>