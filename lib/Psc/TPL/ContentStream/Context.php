<?php

namespace Psc\TPL\ContentStream;

interface Context {

  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage();

  /**
   * @return Psc\Image\Manager
   */
  public function getImageManager();

  /**
   * @return Psc\CMS\Controller\NavigationController
   */
  public function getNavigationController();
}
