<?php

namespace Psc\Form;

use stdClass AS FormData;
use Psc\CMS\EntityFormPanel;
use Psc\CMS\Entity;
use Psc\Doctrine\DCPackage;
use Psc\Data\Set;
use Psc\Code\Code;
use Psc\Data\Type\Type;
use Psc\Data\Type\TypeRuleMapper;
use Psc\UI\Component\ComponentRuleMapper;
use Psc\Form\Validator;
use Closure;
use Webforge\Common\String AS S;

class ValidationPackage extends \Psc\SimpleObject {
  
  protected $componentsValidator;
  
  protected $typeRuleMapper;
  protected $componentRuleMapper;
  
  protected $validationEntity;
  
  public function __construct(ComponentRuleMapper $componentRuleMapper = NULL, TypeRuleMapper $typeRuleMapper = NULL) {
    $this->componentRuleMapper = $componentRuleMapper ?: new ComponentRuleMapper();
    $this->typeRuleMapper = $typeRuleMapper ?: $this->componentRuleMapper->getTypeRuleMapper(); // oder andersrum, nech :)
  }
  
  public function validateIdentifier($id, \Psc\CMS\EntityMeta $entityMeta) {
    $rule = $this->typeRuleMapper->getRule($entityMeta->getIdentifier()->getType());
    
    return $rule->validate($id);
  }
  
  public function validateId($id) {
    $rule = new IdValidatorRule();
    
    return $rule->validate($id);
  }
  
  public function validateNotEmptyString($string) {
    $rule = new NesValidatorRule();
    
    return $rule->validate($string);
  }
  
  /**
   * @param Closure $what bekommt als ersten parameter das Validation Package und kann somit eine Validierung ausführen die eine Exception schmeisst. WIrd die exception geschmissen wird FALSE zurückgegeben ansonsten TRUE. Das ergebnis der Validierung erhalt man mit getCheckValue
   * @return bool
   */
  public function check(Closure $what) {
    try {
      $this->checkValue = $what($this);
      return TRUE;
    } catch (\Exception $e) {
      return FALSE;
    }
  }
  
  public function getCheckValue() {
    return $this->checkValue;
  }
  
  /**
   * Erstellt einen Validator mit den SimpleRules
   * 
   * 'field'=>'nes'
   *
   * $validator->validate('field', $formValue);
   */
  public function createValidator(Array $simpleRules = array()) {
    $validator = new Validator();
    
    if (count($simpleRules) > 0) {
      $validator->addSimpleRules($simpleRules);
    }
    return $validator;
  }
  
  public function validateUploadedFile($field) {
    $rule = new \Psc\Form\UploadFileValidatorRule($field);
    
    return $rule->validate(array()); // liest globals $_FILES aus
  }
  
  /**
   * Validiert alle Felder im Validator anhand der Daten von FormData und benutzt $err um eine ValidationResponse zu erzeugen (falls es Fehler gab)
   *
   * @return FormData (aber validated + cleaned)
   */
  public function validateRequestData(FormData $requestData, Validator $validator, \Psc\Net\ServiceErrorPackage $err) {
    try {
      return (object) $validator->validateFields($requestData); // dieser Cast muss angepasst werden wenn FormData mal was andres ist
    
    } catch (\Psc\Form\ValidatorExceptionList $e) {
      throw $err->validationResponse($e);
    }
  }
  
  /**
   * Erstellt einen ComponentsValidator anhand des EntityFormPanels
   *
   * Der FormPanel muss die Componenten schon erstellt haben sie werden mit getComponents() aus dem Formular genommen
   */
  public function createComponentsValidator(FormData $requestData, EntityFormPanel $panel, DCPackage $dc, Array $components = NULL) {
    $this->validationEntity = $entity = $panel->getEntityForm()->getEntity();
    
    $components = isset($components) ? Code::castCollection($components) : $panel->getEntityForm()->getComponents();
    
    return $this->componentsValidator =
              new ComponentsValidator($this->createFormDataSet($requestData, $panel, $entity),
                                      $components,
                                      $this->componentRuleMapper
                                     );
  }
  
  // dirty: und einmal genutzt in tiptoi GameController für Patch Workaround: todo: schöner bauen
  public function onPostValidation(ComponentsValidator $validator, \Closure $do) {
    $entity = $this->validationEntity;
    
    $validator->addPostValidation(
      new \Psc\Code\Callback(function($componentsValidator, $validatedComponents) use ($entity, $do) {
        $do($entity, $componentsValidator, $validatedComponents);
      })
    );
  }
  
  /**
   * Erstellt aus dem Request und dem FormPanel ein Set mit allen FormularDaten
   *
   * man könnte sich hier auch mal vorstellen die formulardaten im set aufzusplitten
   * Sicherheit: alle Felder die nicht registriert sind durch Componenten oder den Formpanel (getControlFields) schmeissen hier eine Exception
   */
  public function createFormDataSet(FormData $requestData, EntityFormPanel $panel, Entity $entity) {
    $meta = $entity->getSetMeta();
    
    // wir müssen die Spezial-Felder vom EntityFormPanel hier tracken
    foreach ($panel->getControlFields() as $field) {
      $meta->setFieldType($field, Type::create('String'));
    }
    
    // sonderfeld disabled wird ignored
    $meta->setFieldType('disabled', Type::create('Array'));
    
    try {
      $set = new Set((array) $requestData, $meta);

    } catch (\Psc\Data\FieldNotDefinedException $e) {
      throw \Psc\Exception::create("In den FormularDaten befindet sich ein Feld '%s', welches kein Feld aus Entity getSetMeta ist (%s).", implode('.',$e->field), implode(', ',$e->avaibleFields));
    }

    return $set;
  }
  
  public function filterjqxWidgetsFromRequestData(FormData $requestData) {
    $filteredData = $requestData;
    foreach ($requestData as $key => $value) {
      if (S::startsWith($key, 'jqxWidget')) {
        unset($filteredData->$key);
      }
    }
    
    return $filteredData;
  }
  
  /**
   * @param Psc\Form\ComponentsValidator $componentsValidator
   * @chainable
   */
  public function setComponentsValidator(\Psc\Form\ComponentsValidator $componentsValidator) {
    $this->componentsValidator = $componentsValidator;
    return $this;
  }

  /**
   * @return Psc\Form\ComponentsValidator
   */
  public function getComponentsValidator() {
    return $this->componentsValidator;
  }


}
?>