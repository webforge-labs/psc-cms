<?php

namespace Psc\TPL\ContentStream;

use Psc\Doctrine\DCPackageProvider;

interface Context extends DCPackageProvider {

  /**
   * @return Psc\Image\Manager
   */
  public function getImageManager();

  /**
   * @return Psc\CMS\Controller\NavigationController
   */
  public function getNavigationController();
}
