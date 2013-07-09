<?php

namespace Psc\CMS;

use InvalidArgumentException;
use Psc\System\LocaleHelper;

/**
 * Chooses a language from() distinct choices
 * 
 * the first from() call will match
 * if no from() call is matched chooseLanguage() will return the fallback
 * 
 * usage:
 * $chooser = new LanguageChooser(Array $languages)
 * 
 * $currentLanguage = $chooser->fromxxx()->fromxxx()->chooseLanguage();
 */ 
class LanguageChooser {

  protected $languages;
  protected $fallbackLanguage;

  /**
   * Every from adds one chooser to the chain
   * 
   * the first chooser will match
   */
  protected $choosers = array();

  public function __construct(Array $languages, $fallbackLanguage = NULL) {
    $this->languages = array_merge($languages);

    if (count($this->languages) === 0) {
      throw new InvalidArgumentException('Languages cannot be empty');
    }

    $this->fallbackLanguage = $fallbackLanguage ?: $this->languages[0];
  }

  /**
   * @return LanguageChooser
   */
  public static function create(Array $languages, $fallbackLanguage = NULL) {
    return new static($languages, $fallbackLanguage);
  }

  /**
   * 
   * if no language is matched the fallback will be returned
   * 
   * 
   * use \Psc\HTTP\Request
   * 
   * $languageChooser->fromPreferredLocales($request->getPreferredLocales());
   * 
   * @param String[] an ordered Array of preferred locales. Previous ones match first
   */
  public function fromPreferredLocales(Array $locales) {
    $languages = $this->languages;

    $this->choosers[] = function () use ($locales, $languages) {
      foreach ($locales as $locale) {
        try {
          $language = LocaleHelper::toLanguage($locale, $languages);

          if (in_array($language, $languages)) {
            return $language;
          }

        } catch (\Psc\Exception $e) { }
      }

      return NULL;
    };

    return $this;
  }

  public function fromLanguage($language) {
    $languages = $this->languages;
    $this->choosers[] = function () use ($languages, $language) {
      return in_array($language, $languages) ? $language : NULL;
    };

    return $this;
  }

  public function chooseLanguage() {
    foreach ($this->choosers as $chooser) {
      if (($language = $chooser()) !== NULL) {
        return $language;
      }
    }

    return $this->fallbackLanguage;
  }

  public function getFallbackLanguage() {
    return $this->fallbackLanguage;
  }
}
