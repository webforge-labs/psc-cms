<?php

namespace Psc\Test\Controllers;

use Psc\Doctrine\DCPackage;
use Psc\CMS\EntityViewPackage;
use Psc\Form\ValidationPackage;
use Psc\Net\ServiceErrorPackage;

class PageController extends \Psc\CMS\Controller\PageController {

  public function getEntityName() {
    return 'Psc\Entities\Page';
  }
  
}
