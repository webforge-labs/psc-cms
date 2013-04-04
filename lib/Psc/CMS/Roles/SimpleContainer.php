<?php

namespace Psc\CMS\Roles;

use Psc\CMS\UploadManager;
use Psc\Image\Manager as ImageManager;

interface SimpleContainer extends \Psc\CMS\Controller\LanguageAware, FQNSolver {

  public function setRevision($revision);

  public function getRevision();

  /**
   * @return Psc\Image\Manager
   */
  public function getImageManager();
  public function setImageManager(ImageManager $manager);

  /**
   * @return Psc\CMS\UploadManager
   */
  public function getUploadManager();
  public function setUploadManager(UploadManager $manager);
}
