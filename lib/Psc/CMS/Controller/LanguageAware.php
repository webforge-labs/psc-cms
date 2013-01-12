<?php

namespace Psc\CMS\Controller;

interface LanguageAware {
  
  /**
   * @param array $languages alle parameter für setLanguage
   */
  public function setLanguages(Array $languages);
  
  /**
   * @return array
   */
  public function getLanguages();
  
  /**
   * @param string $lang
   */
  public function setLanguage($lang);
  
  /**
   * @return string
   */
  public function getLanguage();
}
?>