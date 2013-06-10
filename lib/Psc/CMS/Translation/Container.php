<?php

namespace Psc\CMS\Translation;

use Webforge\Translation\ResourceTranslator;
use Webforge\Framework\Package\Package;

class Container {

  /**
   * @var Webforge\Translation\Translator
   */
  protected $translator;

  public function __construct(ResourceTranslator $translator) {
    $this->translator = $translator;
  }


  public function loadTranslationsFromPackage(Package $package) {
    $this->getTranslator()->addResourceDirectory(
      $package->getRootDirectory()->sub('resources/translations/')
    );

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
}
