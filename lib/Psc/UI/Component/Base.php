<?php

namespace Psc\UI\Component;

use Psc\UI\HTML;
use stdClass;
use Psc\UI\Form as f;
use Psc\Code\Code;
use Webforge\Types\Type;
use Psc\HTML\Tag AS HTMLTag;
use Psc\CMS\Component;
use Psc\Data\Type\TypeRuleMapper;

abstract class Base extends \Psc\SimpleObject implements \Psc\CMS\Component, \Psc\HTML\HTMLInterface {
  
  protected $init = FALSE;
  
  public function __construct(TypeRuleMapper $typeRuleMapper = NULL) {
    $this->setUp();
    $this->typeRuleMapper = $typeRuleMapper; // getter injection um speicherplatz zu sparen
  }
  
  protected function setUp() {
  }

  /**
   * Das Label der Komponente (wird im Formular angezeigt)
   *
   * @var mixed 
   */
  protected $label;
  
  /**
   * Eine Kurzbeschreibung oder Infos zur Komponente (wird im Formular angezeigt)
   *
   * ist optional
   * Componenten können selbst steuern ob sie einen Hint anzeigen: (hasHint())
   * @var string
   */
  protected $hint;
  
  /**
   * Der Name des Inhalts der Komponente (ist im Formular der Identifier für die Validierung)
   *
   * in einem HTML-Formular ist dies also der Name für die POST/GET-Variable (z.b. Attribut "name" in <input type="text" name="" />
   */
  protected $name;
  
  /**
   * Die Daten, die in der Komponente dargestellt werden
   *
   * der Inhalt passt zum Typ: $type
   * @var $this->type->getInterface()
   */
  protected $value;
  
  /**
   * Der Typ der Daten, die in der Komponente dargestellt werden
   * 
   * @var webforge\Types\Type
   */
  protected $type;
  
  /**
   * @var Psc\Form\ValidatorRule
   */
  protected $validatorRule;
  
  /**
   * kann benutzt werden um den Type nach einer Rule zu befragen,w enn man die Rule nicht selbst verwalten will
   */
  protected $typeRuleMapper;

  public function init() {
    if (!$this->init) {
      $this->doInit();
      $this->init = TRUE;
    }
    return $this;
  }
  
  protected function doInit() {
    $this->initValidatorRule();
  }
  
  protected function initValidatorRule() {
  }
  
  protected function assertInitProperties(array $properties) {
    foreach ($properties as $property) {
      if (!isset($this->$property)) {
        throw new InitException('Property: '.$property.' muss für init gesetzt sein');
      }
    }
  }
  
  /**
   * Gibt den Namen der Componente zurück
   *
   * also TextField oder EmailPicker oder ComboBox oder ComboDropBox usw
   */
  public function getComponentName() {
    return Code::getClassName(Code::getClass($this));
  }
  
  abstract public function getInnerHTML();
  
  /**
   *
   * es ist besser hier getInnerHTML implementieren zu lassen, da wir so immer automatisch wrappen
   * und die Subklassen besser voneinander ableiten können
   */
  public function getHTML() {
    return $this->wrapHTML(
      $this->getInnerHTML()
    );
  }
  
  public function html() {
    return $this->getHTML();
  }
  
  public function wrapHTML($componentContent) {
    $forClass = 'component-for-'.implode('-', (array) $this->getFormName());
    
    $wrapper = HTML::tag('div', new stdClass, array('class'=>array('component-wrapper','input-set-wrapper',$forClass)));
    $wrapper->content->component = $componentContent;
    $wrapper->content->hint = $this->hasHint() ? f::hint($this->hint) : NULL;
    $wrapper->content->break = $this->hasHint() && !$this->isBlock($componentContent) ? '<br />' : NULL;
    $wrapper->content->js = NULL;
    $wrapper->setContentTemplate("%component%\n%js%%break%\n%hint%");
    
    return $wrapper;
  }
  
  public function getWrappedComponent(HTMLTag $wrapper) {
    return $wrapper->content->component;
  }
  
  /**
   * gibt dies TRUE zurück wird z. B. in wrapHTML() kein Umbruch vor dem Hint gemacht
   * @return bool
   */
  protected function isBlock($componentContent = NULL) {
    return $componentContent instanceof \Psc\HTML\Tag &&
      $componentContent->getTag() === 'div' ||
      $componentContent->getTag() === 'fieldset'
    ;
  }
  
  /**
   * @return bool
   */
  public function hasHint() {
    return isset($this->hint);
  }
  
  /**
   *
   * kann dafür benutzt werden die ValidationRule mit den richtigen Parametern aus dem Formular zu setzen
   * z. B. wenn man Abhängigkeiten zu anderen Componenten hat oder man Einstellungen in das Formular eingebaut hat
   */
  public function onValidation(\Psc\Form\ComponentsValidator $validator) {
  }
  
  
  protected function setSubFormName(Component $component, $subComponent = NULL) {
    if (isset($subComponent)) {
      $component->setFormName(array($this->getFormName(), $subComponent));
    } else {
      $component->setFormName($this->getFormName());
    }
  }
  
  public function setFormName($name) {
    if ((is_string($name) && $name != '') || is_array($name) && count($name) != 0) {
      $this->name = $name;
      return $this;
    } else {
      throw new \InvalidArgumentException('name muss string oder array sein und darf nicht leer sein');
    }
  }
  
  public function hasValidatorRule() {
    return isset($this->validatorRule);
  }
  
  /**
   * @param string|Psc\Form\ValidatorRule $validatorRule kann auch sowas wie "Id" oder "IdValidatorRule" sein. Wird der erste Parameter von ComponentRuleMapper->createRule()
   * @chainable
   */
  public function setValidatorRule($validatorRule) {
    $this->validatorRule = $validatorRule;
    return $this;
  }

  /**
   * @return string|Psc\Form\ValidatorRule
   */
  public function getValidatorRule() {
    return $this->validatorRule;
  }


  public function getFormName() {
    return $this->name;
  }

  public function getFormLabel() {
    return $this->label;
  }

  public function setFormLabel($label) {
    $this->label = $label;
    return $this;
  }
  
  public function getFormValue() {
    return $this->value;
  }
  

  public function getValue() {
    return $this->value;
  }

  public function setFormValue($value) {
    $this->value = $value;
    return $this;
  }
  
  /**
   * @param string $hint
   * @chainable
   */
  public function setHint($hint = NULL) {
    $this->hint = $hint;
    return $this;
  }

  /**
   * @return string
   */
  public function getHint() {
    return $this->hint;
  }

  /**
   * @return Psc\Code\Type\Type
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @return Psc\Code\Type\Type
   */
  public function setType(Type $type) {
    $this->type = $type;
    return $this;
  }
  
  public function __toString() {
    return sprintf("Component<%s> '%s'", $this->getComponentName(), $this->getFormName());
  }
  
  /**
   * @param Psc\Data\Type\TypeRuleMapper $typeRuleMapper
   * @chainable
   */
  public function setTypeRuleMapper(\Psc\Data\Type\TypeRuleMapper $typeRuleMapper) {
    $this->typeRuleMapper = $typeRuleMapper;
    return $this;
  }

  /**
   * @return Psc\Data\Type\TypeRuleMapper
   */
  public function getTypeRuleMapper() {
    if (!isset($this->typeRuleMapper)) {
      $this->typeRuleMapper = new TypeRuleMapper();
    }
    return $this->typeRuleMapper;
  }
}
