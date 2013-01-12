<?php

namespace Psc\CMS;

use Psc\Data\ArrayCollection;
use Psc\Code\Code;
use Psc\UI\Form as f;
use Psc\Code\Event\Manager;
use Psc\JS\Helper as jsHelper;
use Psc\HTML\JooseBase;

/**
 * Diese Klasse ist eine funktionale Erweiterung der Psc\CMS\Form
 *
 * eine Komponente ist ein Input-Feld oder eine Kombination aus diesen, die einen bestimmten Datentyp modelliert
 */
class ComponentsForm extends \Psc\CMS\Form implements \Psc\Code\Event\Dispatcher {
  
  const EVENT_COMPONENT_ADD = 'Psc\CMS\ComponentsForm.ComponentAdd';
  
  /**
   * Die Komponenten der Form
   * 
   * die Komponenten sind nach Schlüssel sortiert
   */
  protected $components;
  
  protected $manager;
  
  public function __construct($formId = NULL, Manager $manager = NULL) {
    parent::__construct($formId);
    $this->manager = $manager ?: new Manager();
  }
  
  protected function setUp() {
    parent::setUp();
    
    $this->components = new ArrayCollection();
  }
  
  /**
   * @return Collection
   */
  public function getComponents() {
    return $this->components;
  }
  
  /**
   * @chainable
   */
  public function addComponent(Component $component) {
    $this->getManager()->dispatchEvent(self::EVENT_COMPONENT_ADD, compact('component'), $this);
    
    $this->components->add($component);
    return $this;
  }
  
  /**
   * @param int $index 0-basierend
   */
  public function getComponent($index) {
    return $this->components->get($index);
  }
  
  
  public function removeComponent(Component $component) {
    if ($this->components->contains($component)) {
      $this->components->removeElement($component);
    }
    return $this;
  }


  protected function doInit() {
    //foreach ($this->components as $key=>$component) {
    //  $this->setContent('component'.$key, $component->getHTML());
    //}
    parent::doInit();
  }

  /**
   * 
   * $content .= f::inputSet(
      f::input(
        $form->createTypeSelect('\SerienLoader\Status',$episode->getStatus())
      )
   */
  public function createTypeSelect($typeClass, $selected, $label = NULL, $field = NULL) {
    $type = $typeClass::instance();
    
    $values = $type->getValues();
    $options = array_combine($values, array_map('ucfirst',$values));
    
    if (!isset($field)) {
      $field = lcfirst(Code::getClassName($typeClass)); // sowas wie \tiptoi\SpeakerType => speakerType
    }
    
    if (!isset($label)) {
      $label = ucfirst($field);
    }
    
    return f::select($label,lcfirst($field), $options, $selected);
  }
  
  
  public function sortComponentsBy($property) {
    Code::value($property, 'formName','formValue','formLabel','componentName');
    
    $getter = Code::castGetter($property);

    $sorting = function ($c1, $c2) use ($getter) {
      return strcmp($getter($c1), $getter($c2));
    };
    
    $this->sortComponents($sorting);
    
    return $this;
  }
  
  /**
   * Verändert die aktuelle Reihenfolge der Componenten
   *
   * $sorting kann entweder ein Closure sein welches als Parameter 2 Komponenten bekommt und dann entscheidet welche weiter vorne stehen soll.
   * oder ein array von property Names. Diese werden dann in der Reihenfolge wie im Array gesetzt, alle restlichen (nicht im array angegebenen) Componenten werden ans Ende gehängt
   * @param Closure|array $sorting kann ein Array von property-Names sein oder eine Closure die 2 Parameter erhält und eine Sortierfunktion ist returns: -1|0|1
   */
  public function sortComponents($sorting) {
    if (is_array($sorting)) {
      $order = array_flip($sorting); // $name => $position
      // wir erstellen unseren name => componenten index
      $components = \Psc\Doctrine\Helper::reindex($this->components->toArray(),'getFormName');
      // wir fügen alle Komponenten hinzu, die nicht in dem Index drin sind (hängen sie nach hinten an)
      foreach($components as $name => $component) {
        if (!array_key_exists($name, $order)) {
          $order[$name] = count($order);
        }
      }
      
      $sorting = function ($c1, $c2) use ($order) {
        // @TODO formName kann auch ein array sein?
        if (($n1 = $c1->getFormName()) === ($n2 = $c2->getFormName())) {
          return 0;
        }
        
        return $order[$n1] > $order[$n2] ? 1 : -1;
      };
    }
    
    if (!($sorting instanceof \Closure)) {
      throw new \InvalidArgumentException('sortComponents muss eine Closure als Parameter haben (oder ein Array)');
    }
    
    $this->components->sortBy($sorting);
    
    return $this;
  }
  
  /**
   * @return Component|NULL
   */
  public function getComponentByFormName($name) {
    foreach ($this->getComponents() as $component) {
      if ($component->getFormName() === $name) {
        return $component;
      }
    }
    return NULL;
  }
  
  public function getManager() {
    return $this->manager;
  }
}
?>