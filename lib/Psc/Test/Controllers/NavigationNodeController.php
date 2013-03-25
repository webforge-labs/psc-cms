<?php

namespace Psc\Test\Controllers;

use Psc\Doctrine\DCPackage;
use Psc\CMS\EntityViewPackage;
use Psc\Form\ValidationPackage;
use Psc\Net\ServiceErrorPackage;

class NavigationNodeController extends \Psc\CMS\Controller\AbstractEntityController {

  public function __construct(DCPackage $dc = NULL, EntityViewPackage $ev = NULL, ValidationPackage $v = NULL, ServiceErrorPackage $err = NULL) {
    $this->dc = $dc;
    $this->ev = $ev;
    $this->v = $v;
    $this->err = $err;
  }
  
  public function getEntityName() {
    return 'Psc\Entities\NavigationNode';
  }
  
}
