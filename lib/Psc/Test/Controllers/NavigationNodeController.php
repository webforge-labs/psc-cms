<?php

namespace Psc\Test\Controllers;

use Psc\Doctrine\DCPackage;
use Psc\CMS\EntityViewPackage;
use Psc\Form\ValidationPackage;
use Psc\Net\ServiceErrorPackage;

class NavigationNodeController extends \Psc\CMS\Controller\NavigationController {

  public function getEntityName() {
    return 'Psc\Entities\NavigationNode';
  }
  
}
