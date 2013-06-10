<?php

namespace Psc\CMS\Translation;

use Webforge\Translation\Translator;

class Container {

  /**
   * @var Webforge\Translation\Translator
   */
  protected $translator;

  public function __construct(Translator $translator) {
    $this->translator = $translator;
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
