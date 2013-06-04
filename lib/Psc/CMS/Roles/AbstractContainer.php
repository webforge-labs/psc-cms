<?php

namespace Psc\CMS\Roles;

use Webforge\Framework\Package\Package;
use Webforge\Framework\Package\ProjectPackage;
use Psc\Doctrine\DCPackage;
use Psc\TPL\ContentStream\Converter AS ContentStreamConverter;
use Webforge\Translation\ArrayTranslator;

abstract class AbstractContainer extends AbstractControllerContainer implements Container, \Psc\TPL\ContentStream\Context {

  /**
   * @var string
   */
  protected $defaultControllersNamespace;

  /**
   * @var Webforge\Framework\Package\ProjectPackage
   */
  protected $projectPackage;

  /**
   * @var Webforge\Framework\Package\Package
   */
  protected $package;

  /**
   * @var array
   */
  private $translations;

  public function __construct($controllersNamespace, DCPackage $dc, Array $languages, $language,  ContentStreamConverter $contentStreamConverter = NULL) {
    parent::__construct($controllersNamespace ?: $this->defaultControllersNamespace, $dc, $languages, $language, $contentStreamConverter);
  }

  public function setPackage(Package $package) {
    $this->package = $package;
  }

  public function getPackage() {
    if (!isset($this->package)) {
      $this->package = $GLOBALS['env']['container']->webforge->getLocalPackage();
    }
    
    return $this->package;
  }


  public function getProjectPackage() {
    if (!isset($this->projectPackage)) {
      $this->projectPackage = new ProjectPackage($this->getPackage());
    }

    return $this->projectPackage;
  }

  /**
   * @return Webforge\Common\System\Dir
   */
  public function getPackageDir($sub) {
    return $this->getPackage()->getRootDirectory()->sub($sub);
  }

  /**
   * @return Webforge\Translation\Translator
   */
  public function getTranslator() {
    if (!isset($this->translator)) {
      $package = $this->getProjectPackage();

      $this->translator = new ArrayTranslator(
        $this->getLanguage(),
        $this->getTranslations($package),
        array($package->getDefaultLanguage())
      );
    }

    return $this->translator;
  }

  protected function getTranslations(ProjectPackage $package) {
    if (!isset($this->translations)) {
      $this->translations = array();
      foreach ($this->getLanguages() as $locale)  {// or: this->getLanguages() ??
        $this->translations[$locale] = $package->getConfiguration()->get(array('translations', $locale), array());
      }
    }

    return $this->translations;
  }

  public function setLanguage($lang) {
    parent::setLanguage($lang);

    if (isset($this->translator)) {
      $this->translator->setLocale($this->getLanguage());
    }

    return $this;
  }
}
