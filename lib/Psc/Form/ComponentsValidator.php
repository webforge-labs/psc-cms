<?php

namespace Psc\Form;

use Psc\Code\Code;
use Psc\Data\Set;
use Psc\Data\ArrayCollection;
use Psc\Data\SetMeta;
use Psc\UI\Component\ComponentRuleMapper;
use Psc\UI\Component\SelfValidatingComponent;
use Psc\CMS\Component;

/**
 * Ein Formular-Set-Validator
 *
 * erhält ein Set aus dem Frontend mit $_POST Daten.
 * Validiert anhand der ArrayCollection der formComponents (wandelt strings / json, etc um)
 * benutzt dafür Form\ValidatorRules
 *
 * @TODO FIXME: validateComponents cannot be used twice with different validator rules
 */
class ComponentsValidator extends \Psc\Form\Validator {
  
  /**
   * Das Set in das die validierten Daten eingetragen werden
   * 
   */
  protected $set;
  
  protected $ruleMapper;
  
  protected $components;
  
  protected $formData;

  /**
   * wenn $this->exceptionList TRUE ist werden die ValidationExceptions gesammelt und dann eine ValidatorExceptionList geworfen
   * wenn FALSE wird direkt die erste ValidatorException geworfen
   */
  protected $exceptionList = FALSE;
  
  protected $validatedComponents = array();
  
  /**
   * @var Psc\Code\CallableObject[]
   */
  protected $postValidations = array();
  
  public function __construct(Set $formData, ArrayCollection $formComponents, ComponentRuleMapper $ruleMapper = NULL) {
    $this->dataProvider = $this->formData = $formData;
    $this->set = new Set();
    $this->ruleMapper = $ruleMapper ?: new ComponentRuleMapper();
    $this->components = $formComponents;
    
    parent::__construct();
  }
  
  /**
   * Validiert alle Daten anhand der Meta-Daten von den FormDaten
   *
   * optionals müssen hiervor gesetzt werden
   *
   * wenn $this->exceptionList TRUE ist werden die ValidationExceptions gesammelt und dann eine ValidatorExceptionList geworfen
   * wenn FALSE wird direkt die erste ValidatorException geworfen
   * 
   */
  public function validateSet() {
    $this->validatedComponents = array(); // jede Componente nur einmal validieren, wir führen buch
    $exceptions = array();
    foreach ($this->components as $component) {
      try {
        $this->validateComponent($component);
      
      } catch (ValidatorException $e) {
        if ($this->exceptionList) {
          $exceptions[] = $e;
        } else {
          throw $e;
        }
      }
    }
    
    if ($this->exceptionList && count($exceptions) > 0) {
      throw new ValidatorExceptionList($exceptions);
    }
    
    $this->postValidation();
    return $this;
  }
  
  /**
   * @return Component
   */
  public function validateComponent(Component $component) {
    if (!in_array($component, $this->validatedComponents)) { // nicht key index zur sicherheit (falls mal namen doppelt oder arrays?)
      $field = $component->getFormName();
    
      $component->onValidation($this); // kann validateComponent für andere Aufrufen (das kann einen Ring ergeben)

      // if we add the rule here, we add it everytime on validateComponent, but
      // this allows the component onValidation() to change its validatorrule
      // @FIXME: this will break if rule changes and component is validated again, the old rule would be there anyway
      //(and match first)
      $this->addRule(
        $rule = $this->ruleMapper->getRule($component),
        $component->getFormName()
      );
      
      if ($component instanceof SelfValidatingComponent) {
        $component->setFormValue($value = $this->validate($field, NULL));
        $this->set->set($field, $component->validate($this), $component->getType());
      } else {
        // we compute the validated value
        $this->set->set($field, $value = $this->validate($field, NULL), $component->getType());
        $component->setFormValue($value);
      }
      
      
      $this->validatedComponents[] = $component; // mark visited
    }
    
    return $component;
  }
  
  protected function postValidation() {
    foreach ($this->postValidations as $callback) {
      $callback->call(array($this, $this->validatedComponents));
    }
    return $this;
  }
  
  /**
   * @return Component
   * @throws \InvalidArgumentException wenn es die Component nicht gibt
   */
  public function getComponentByFormName($name) {
    foreach ($this->components as $component) {
      if ($component->getFormName() === $name) {
        return $component;
      }
    }
    
    throw new \InvalidArgumentException('Die Componente "'.$name.'" existiert nicht');
  }
  
  /**
   * Fügt ein Callback nach der Validierung hinzu
   *
   * Der callback bekommt als ersten dynamischen Parameter den ComponentsValidator übergeben
   * als zweiten die componenten die validiert wurden
   *
   * $callback(ComponentsValidator $cv, array $components)
   */
  public function addPostValidation(\Psc\Code\CallableObject $callback) {
    $this->postValidations[] = $callback;
    return $this;
  }
  
  public function getSet() {
    return $this->set;
  }
}
?>