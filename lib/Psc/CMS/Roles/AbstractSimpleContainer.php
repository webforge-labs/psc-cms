<?php

namespace Psc\CMS\Roles;

use Psc\Doctrine\DCPackage;
use Psc\CMS\Controller\LanguageAware;
use Psc\TPL\ContentStream\Converter as ContentStreamConverter;
use Psc\CMS\UploadManager;
use Psc\Doctrine\EntityRepository;
use Psc\Image\Manager as ImageManager;

abstract class AbstractSimpleContainer extends \Psc\SimpleObject implements SimpleContainer {

  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   * @var array
   */
  protected $languages;
  
  /**
   * @var string
   */
  protected $language;
  
  /**
   * @var string
   */
  protected $revision = 'default';

  /**
   * @var Psc\TPL\ContentStream\Converter
   */
  protected $contentStreamConverter;
  
  /**
   * @var Psc\CMS\UploadManager
   */
  protected $uploadManager;

  /**
   * @var Psc\CMS\ImageManager
   */
  protected $imageManager;
  
  /**
   * @var Psc\Doctrine\EntityRepository
   */
  protected $navigationRepository;

  public function __construct(DCPackage $dc, Array $languages, $language,  ContentStreamConverter $contentStreamConverter = NULL) {
    $this->dc = $dc;
    $this->setLanguages($languages);
    $this->setLanguage($language);
    $this->contentStreamConverter = $contentStreamConverter;
  }

  /**
   * @return CoMun\ContentStreamConverter
   */
  public function getContentStreamConverter() {
    if (!isset($this->contentStreamConverter)) {
      $this->contentStreamConverter = $this->createContentStreamConverter();
      
    $this->contentStreamConverter->getContext()
      ->setImageManager(
        $this->getImageManager()
      )
      ->setLanguages($this->languages)
      ->setLanguage($this->language)
      ->setDoctrinePackage($this->dc)
      ->setUploadManager(
        $this->getUploadManager()
      )
      ;
    }
    
    return $this->contentStreamConverter;
  }

  protected function createContentStreamConverter() {
    return new ContentStreamConverter();
  }

  protected function createImageManager() {
    return new ImageManager($this->getRoleFQN('Image'), $this->dc->getEntityManager());
  }


  /**
   * @return Psc\CMS\UploadManager
   */
  public function getUploadManager() {
    if (!isset($this->uploadManager)) {
      $this->uploadManager = new UploadManager($this->getRoleFQN('File'), $this->dc);
    }
    return $this->uploadManager;
  }

  /**
   * @return Psc\Image\Manager
   */
  public function getImageManager() {
    if (!isset($this->imageManager)) {
      $this->imageManager = $this->createImageManager();
    }

    return $this->imageManager;
  }


  /**
   * @param Psc\TPL\ContentStream\Converter $contentStreamConverter
   */
  public function setContentStreamConverter(ContentStreamConverter $contentStreamConverter) {
    $this->contentStreamConverter = $contentStreamConverter;
    return $this;
  }

  /**
   * @return Psc\Doctrine\EntityRepository
   */
  public function getNavigationRepository() {
    if (!isset($this->navigationRepository)) {
      $this->navigationRepository = $this->dc->getRepository($this->getRoleFQN('NavigationNode'));
    }
    return $this->navigationRepository;
  }
  
  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage() {
    return $this->dc;
  }
  
  /**
   * @param array $languages alle parameter für setLanguage
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
   * @param string $lang
   */
  public function setLanguage($language) {
    $this->language = $language;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getLanguage() {
    return $this->language;
  }
  
  /**
   * @param string $revision
   * @chainable
   */
  public function setRevision($revision) {
    $this->revision = $revision;
    return $this;
  }

  /**
   * @return string
   */
  public function getRevision() {
    return $this->revision;
  }
}
?>