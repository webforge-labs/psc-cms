<?php

namespace Psc\Test\Controllers;

class TagController extends \Psc\CMS\Controller\AbstractEntityController {
  
  public function getEntityName() {
    return 'Psc\Doctrine\TestEntities\Tag';
  }
  
}

?>