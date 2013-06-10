<?php

namespace Psc\Test;

class ArticleSortingController extends \Psc\CMS\Controller\AbstractEntityController implements \Psc\CMS\Controller\SortingController {
  
  public function getEntityName() {
    return 'Psc\Doctrine\TestEntities\Article';
  }
  
  public function getSortField() {
    return 'sort';  // ist quatsch, weils das für den article natürlich nicht gibt
  }
}
