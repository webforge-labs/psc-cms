<?php

namespace Psc\CMS\Translation;

use Webforge\Translation\Translator;

class Container implements Translator {

  /**
   * @var Webforge\Translation\Translator
   */
  protected $translator;

  public function __construct(Translator $translator) {
    $this->translator = $translator;
  }

  public function trans($id, Array $parameters = array(), $domain = NULL, $locale = NULL) {
    return $this->translator->trans($id, $parametres, $domain, $locale);
  }

  public function setLocale($locale) {
    $this->translator->setLocale($locale);
    return $this;
  }

  public function getLocale() {
    return $this->translator->getLocale();
  }

  public function setFallbackLocales(Array $locales) {
    $this->translator->setFallbackLocales($locales);
    return $this;
  }
}
