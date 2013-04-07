<?php

namespace Psc\Doctrine;

interface DCPackageProvider {

 /**
  * @return Psc\Doctrine\DCPackage
  */
  public function getDoctrinePackage();

}
