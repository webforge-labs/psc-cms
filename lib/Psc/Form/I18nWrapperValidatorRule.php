<?php

namespace Psc\Form;

/**
 * 
 */
class I18nWrapperValidatorRule extends \Psc\SimpleObject implements ValidatorRule {
  
  /**
   * @var array
   */
  protected $languages;
  
  /**
   * @var Psc\Form\ValidatorRule
   */
  protected $wrappedRule;
  
  public function __construct(ValidatorRule $wrappedRule, Array $languages) {
    $this->setLanguages($languages);
    $this->setWrappedRule($wrappedRule);
  }
  
  /**
   * Validiert alle für alle sprachen der Data die Daten der Inneren Componente
   * 
   * @return $data
   */
  public function validate($data) {
    if ($data === NULL) throw $this->emptyException();
    if (!is_array($data)) throw $this->invalidArgument(1, $data, 'Array', __FUNCTION__);
    
    foreach ($this->languages as $lang) {
      if (!array_key_exists($lang, $data)) {
        throw $this->emptyException();
      }
      
      try {
        $data[$lang] = $this->wrappedRule->validate($data[$lang]);
      } catch (EmptyDataException $e) {
        throw $this->emptyException($e);
      }
    }
    
    return $data;
  }
  
  protected function emptyException($previous = NULL) {
    return EmptyDataException::factory(array_fill_keys($this->languages, NULL), $previous);
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
  
  /**
   * @param Psc\Form\ValidatorRule $wrappedRule
   */
  public function setWrappedRule(ValidatorRule $wrappedRule) {
    $this->wrappedRule = $wrappedRule;
    return $this;
  }
  
  /**
   * @return Psc\Form\ValidatorRule
   */
  public function getWrappedRule() {
    return $this->wrappedRule;
  }
}
?>