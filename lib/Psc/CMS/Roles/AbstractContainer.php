<?php

namespace Psc\CMS\Roles;

use Webforge\Framework\Package\Package;
use Webforge\Framework\Package\ProjectPackage;
use Psc\Doctrine\DCPackage;
use Psc\TPL\ContentStream\Converter AS ContentStreamConverter;
use Webforge\Translation\ArrayTranslator;
use Psc\CMS\Translation\Container as TranslationContainer;

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


  protected $translationContainer;

  public function __construct($controllersNamespace, DCPackage $dc, Array $languages, $language,  ContentStreamConverter $contentStreamConverter = NULL) {
    parent::__construct($controllersNamespace ?: $this->defaultControllersNamespace, $dc, $languages, $language, $contentStreamConverter);
  }

  public function setPackage(Package $package) {
    $this->package = $package;
    return $this;
  }

  public function getPackage() {
    if (!isset($this->package)) {
      $this->package = $this->getWebforge()->getLocalPackage();
    }
    
    return $this->package;
  }

  protected function getWebforge() {
    return $GLOBALS['env']['container']->webforge;
  }

  public function getProjectPackage() {
    if (!isset($this->projectPackage)) {
      $this->projectPackage = $this->getWebforge()->getProjectsFactory()->fromPackage($this->getPackage());
    }

    return $this->projectPackage;
  }

  public function getProject() {
    return $this->getProjectPackage();
  }

  /**
   * @return Webforge\Common\System\Dir
   */
  public function getPackageDir($sub) {
    return $this->getPackage()->getRootDirectory()->sub($sub);
  }

  /**
   * @return Psc\CMS\Translation\Container
   */
  public function getTranslationContainer() {
    if (!isset($this->translationContainer)) {
      $this->translationContainer = new TranslationContainer(
        new ArrayTranslator(
          $this->getLanguage(),
          array(),
          // fallback:
          array('en')
        )
      );

      $localPackage = $this->getPackage();

      if ($localPackage->getIdentifier() !== 'pscheit/psc-cms') {
        $this->translationContainer->loadTranslationsFromPackage($this->getWebforge()->getVendorPackage('pscheit/psc-cms'));
      }

      $this->translationContainer->loadTranslationsFromPackage($this->getPackage());
      $this->translationContainer->loadTranslationsFromProjectPackage($this->getProjectPackage());
    }

    return $this->translationContainer;
  }

  /**
   * @chainable
   */
  public function setTranslationContainer(TranslationContainer $translationContainer) {
    $this->translationContainer = $translationContainer;
    return $this;
  }

  /**
   * @return Webforge\Translation\Translator
   */
  public function getTranslator() {
    return $this->getTranslationContainer()->getTranslator();
  }

  public function setLanguage($lang) {
    parent::setLanguage($lang);

    if (isset($this->translationContainer)) {
      $this->translationContainer->setLocale($this->getLanguage());
    }

    return $this;
  }

  /**
   * @return Webforge\Common\System\Container
   */
  public function getSystemContainer() {
    return $this->getWebforge()->getSystemContainer();
  }
}
