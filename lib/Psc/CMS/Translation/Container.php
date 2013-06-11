<?php

namespace Psc\CMS\Translation;

use Webforge\Translation\ResourceTranslator;
use Webforge\Framework\Package\Package;
use Webforge\Framework\Package\ProjectPackage;

class Container {

  /**
   * @var Webforge\Translation\Translator
   */
  protected $translator;

  public function __construct(ResourceTranslator $translator) {
    $this->translator = $translator;
  }

  public function loadTranslationsFromPackage(Package $package) {
    $translationsDir = $package->getRootDirectory()->sub('resources/translations/');

    if ($translationsDir->exists()) {
      $this->getTranslator()->addResourceDirectory($translationsDir);
    }

    return $this;
  }

  public function loadTranslationsFromProjectPackage(ProjectPackage $package, $domain = NULL) {
    foreach ($package->getConfiguration()->get(array('translations'), array()) as $locale => $translations) {
      $this->translator->addResource('array', $translations, $locale, $domain);
    }

    return $this;
  }

  /**
   * @return Webforge\Translation\Translator
   */
  public function getTranslator() {
    return $this->translator;
  }

  /**
   * Changes the current language
   */
  public function setLocale($locale) {
    $this->translator->setLocale($locale);
    return $this;
  }

  public function getLocale() {
    return $this->translator->getLocale();
  }
}
