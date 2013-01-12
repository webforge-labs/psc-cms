<?php

namespace Psc\CMS\Controller;

/**
 * 
 */
class LanguageAwareController extends \Psc\SimpleObject implements LanguageAware {
  
  /**
   * @var array
   */
  protected $languages;
  
  /**
   * @var string
   */
  protected $language;
  
  public function __construct(Array $languages = array(), $language = NULL) {
    $this->languages = $languages;
    $this->language = $language;
  }
  
	/**
   * @param Array $languages
   * @chainable
   */
  public function setLanguages(Array $languages) {
    $this->languages = $languages;
    return $this;
  }

  /**
   * @return Array
   */
  public function getLanguages() {
    return $this->languages;
  }

	/**
   * @param string $language
   * @chainable
   */
  public function setLanguage($language) {
    if (!in_array($language,$this->languages)) {
      throw new \Psc\Code\WrongValueException(
        sprintf("Language '%s' is not allowed. Allowed are: %s, ",
                $language,
                implode(',',$this->languages)
        )
      );
    }
    
    $this->language = $language;
    return $this;
  }

  /**
   * @return string
   */
  public function getLanguage() {
    return $this->language;
  }  
}
?>