<?php

namespace Psc\UI\Component;

use Psc\CMS\ComponentMapper;
use Psc\UI\HTML;
use Psc\UI\jqx\Tabs;
use Psc\UI\jqx\Tab;
use Psc\Data\Type\Type;

/**
 * 
 */
class I18nWrapper extends JavaScriptBase implements JavaScriptComponent {
  
  /**
   * @var Psc\CMS\ComponentMapper
   */
  protected $componentMapper;
  
  /**
   * @var array
   */
  protected $languages;
  
  protected $wrappedType;
  
  protected $components;
  
  public function dpi(Type $wrappedType, Array $languages, ComponentMapper $componentMapper = NULL) {
    $this->wrappedType = $wrappedType;
    $this->languages = $languages;
    $this->setComponentMapper($componentMapper ?: new ComponentMapper());

    foreach ($this->languages as $lang) {
      $this->components[$lang] = $this->componentMapper->inferComponent($this->wrappedType);
    }
    
    return $this; // ganz wichtig wegen chaining in i18nType
  }

  public function getInnerHTML() {
    $componentTabs = array();
    
    foreach ($this->components as $lang => $component) {
      $componentTabs[] = new Tab(HTML::esc($lang), $component->getInnerHTML()); // ein bildchen für lang?
    }
    
    $tabs = new Tabs($componentTabs);
    $tabs->setAutoLoad(FALSE);
    
    return $tabs->html();
  }
  
  protected function initValidatorRule() {
    // alternativ: die Rule aus einer der inneren Components nehmen?
    $this->validatorRule = $this->getTypeRuleMapper()->createRule('I18nWrapperValidatorRule', array(
      $this->getTypeRuleMapper()->getRule($this->wrappedType),
      $this->getLanguages()
    ));
  }
  
  public function getJavaScript() {
    return $this->createJooseSnippet(
      'Psc.UI.jqx.I18nWrapper',
      array(
        'languages'=>$this->languages,
        'widget'=>$this->findInJSComponent('.webforge-jqx-tabs'), // das ist dann der wrapper der wrapper-componente
      )
    );
  }

  public function setFormLabel($label) {
    parent::setFormLabel($label);
    
    foreach ($this->components as $lang => $component) {
      $component->setFormLabel($label); // .' ('.$lang.')');
    }
    return $this;
  }

  public function setFormValue($i18nValue) {
    parent::setFormValue($i18nValue);
    
    foreach ((array) $i18nValue as $lang => $value) {
      $this->components[$lang]->setFormValue($value);
    }

    return $this;
  }
  
  public function setFormName($name) {
    parent::setFormName($name);
    
    // meinName[de] setzen
    foreach ($this->components as $lang => $component) {
      $this->setSubFormName($component, $lang);
    }
    
    return $this;
  }
  
  /**
   * @param Psc\CMS\ComponentMapper $componentMapper
   */
  public function setComponentMapper(ComponentMapper $componentMapper) {
    $this->componentMapper = $componentMapper;
    return $this;
  }
  
  /**
   * @return Psc\CMS\ComponentMapper
   */
  public function getComponentMapper() {
    return $this->componentMapper;
  }
  
  /**
   * @param array $languages
   */
  public function setLanguages(Array $languages) {
    $this->languages = $languages;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getLanguages() {
    return $this->languages;
  }
}
?>