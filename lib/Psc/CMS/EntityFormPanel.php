<?php

namespace Psc\CMS;

use Psc\Data\Type\Type;
use Psc\Code\Event\Manager;
use Psc\Code\Event\CallbackSubscriber;
use Psc\UI\Accordion;
use Psc\CMS\Component;
use Psc\Code\Code;

class EntityFormPanel extends \Psc\UI\FormPanel implements \Psc\CMS\ComponentsCreater {
  
  /**
   * @var array|NULL
   */
  protected $whiteListProperties;

  /**
   * @var Psc\Code\Event\Manager
   */
  protected $manager;
  
  /**
   * @var Psc\CMS\ComponentMapper
   */
  protected $componentMapper;
  
  /**
   * @var Psc\CMS\Labeler
   */
  protected $labeler;
  
  public function __construct($label, TranslationContainer $translationContainer, \Psc\CMS\EntityForm $form, ComponentMapper $mapper = NULL, Labeler $labeler = NULL, Manager $manager = NULL) {
    parent::__construct($label, $translationContainer, $form);
    $this->componentMapper = $mapper ?: new ComponentMapper();
    $this->labeler = $labeler ?: new Labeler();
    $this->manager = $manager ?: new Manager();

    // automatic subscription for entity
    if (($entity = $this->getEntity()) instanceof ComponentsCreaterListener) {
      $this->subscribe($entity);
    }
  }
  
  protected function doInit() {
    // damit die components der form innerhalb des formpanels an der richtigen stelle stehen
    // holen wir uns diese aus der Componentsform
    foreach ($this->form->getComponents() as $key => $component) {
      try {
        $this->content['component'.$key] = $component->html();
      } catch (\Psc\Exception $e) {
        throw new \Psc\Exception('Component '.Code::getClass($component).' verursachte einen Fehler beim HTML-Erzeugen', 0, $e);
      }
    }
    
    parent::doInit();
  }
  
  /**
   * Setzt die Properties, die angezeigt werden sollen
   */
  public function setWhiteListProperties(Array $propertyNames) {
    $this->whiteListProperties = $propertyNames;
    return $this;
  }
  
  /**
   * @return Psc\CMS\Entity
   */
  public function getEntity() {
    return $this->form->getEntity();
  }
  
  public function createComponents() {
    // erstmal müssen wir ja wissen, welche componenten wir erstellen sollen
    // reihenfolge direkt inklusive
    $meta = $this->getEntity()->getSetMeta();
    
    if (isset($this->whiteListProperties)) {
      foreach ($this->whiteListProperties as $property) {
        $this->form->addComponent(
          $this->createComponent($property, $meta->getFieldType($property))
        );
      }
    } else {
      foreach($meta->getTypes() as $property => $propertyType) {
        $this->form->addComponent(
          $this->createComponent($property, $propertyType)
        );
      }
    }
    
    return $this;
  }
  
  /**
   * Fügt dem FormPanel eine Componente hinzu
   */
  public function addComponent(Component $component) {
    $this->form->addComponent($component);
    return $this;
  }
  
  /**
   * @return Psc\UI\Component
   */
  public function createComponent($property, Type $propertyType) {
    try {
      $component = $this->componentMapper->inferComponent($propertyType);
      
      $event = $this->getManager()->dispatchEvent(self::EVENT_COMPONENT_CREATED,
                                         array('component'=>$component,
                                               'creater'=>$this,
                                               'name'=>$property,
                                               'value'=>$this->getEntity()->callGetter($property),
                                               'label'=>$this->labeler->getLabel($property)
                                              ),
                                         $this
                                        );
      
      if (!$event->isProcessed()) {
        $component->setFormName($event->getData()->name);
        $component->setFormValue($event->getData()->value);
        $component->setFormLabel($event->getData()->label);
      } // else: wenn wir hier wollten, dass die component vom event replacebar sein soll, könnten wir hier event->getData()->component nehmen
      
      $component->init(); // der eventListener kann ruhig init() aufrufen, wir stellen hier sicher, dass es aufgerufen wird
      
      return $component;   
    } catch (NoComponentFoundException $e) {
      $e->setMessage($e->getMessage().' property: '.$property);
      // what to do here?
      throw $e;
    }
  }
  
  /**
   * @return Psc\UI\Accordion
   */
  public function createRightAccordion(EntityMeta $entityMeta = NULL, $optionsLabel = 'Options', Array $options = array()) {
    $entity = $this->getEntity();
    
    $accordion = new Accordion(array('autoHeight'=>true, 'active'=>0));
    
    if (isset($entityMeta)) {
      if (!$entity->isNew()) {
        $options[] = $deleteButton = $entityMeta->getAdapter($entity, EntityMeta::CONTEXT_DELETE)->getDeleteTabButton();
      }
    }
    
    $accordion->addSection($optionsLabel, $options);
    
    $this->addAccordion($accordion);
    return $accordion;
  }
  
  /**
   * @return Component|NULL
   */
  public function getComponent($formName) {
    return $this->form->getComponentByFormName($formName);
  }
  
  public function subscribe(ComponentsCreaterListener $listener) {
    $this->getManager()->bind(CallbackSubscriber::create($listener, 'onComponentCreated')
                              ->setArgumentsMapper(function ($event) {
                                                    $d = $event->getData();
                                                    return array($d->component,
                                                                 $d->creater,
                                                                 $event
                                                                 );
                                                   }),
                              self::EVENT_COMPONENT_CREATED);
  }
  
  public function label($property, $label) {
    $this->labeler->addLabelMapping($property, $label);
    return $this;
  }
  
  public function getControlFields() {
    return $this->form->getControlFields();
  }
  
  public function getManager() {
    return $this->manager;
  }
  
  /**
   * @param ComponentMapper $componentMapper
   * @chainable
   */
  public function setComponentMapper(ComponentMapper $componentMapper) {
    $this->componentMapper = $componentMapper;
    return $this;
  }

  /**
   * @return ComponentMapper
   */
  public function getComponentMapper() {
    return $this->componentMapper;
  }

  public function getEntityForm() {
    return $this->form;
  }
  
  public function setRequestMeta(RequestMeta $requestMeta) {
    $this->form->setRequestMeta($requestMeta);
    return $this;
  }
}
?>