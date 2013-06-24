<?php

namespace Psc\TPL\ContentStream;

use Psc\Doctrine\DCPackageProvider;
use Psc\Image\ManagerProvider as ImageManagerProvider;

interface Context extends DCPackageProvider, ImageManagerProvider {

  /**
   * @return Psc\CMS\Controller\NavigationController
   */
  public function getNavigationController();

}
