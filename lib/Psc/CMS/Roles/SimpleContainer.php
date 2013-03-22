<?php

namespace Psc\CMS\Roles;

interface SimpleContainer extends \Psc\CMS\Controller\LanguageAware, FQNSolver {

  public function setRevision($revision);

  public function getRevision();

}
?>