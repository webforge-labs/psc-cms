<?php

namespace Psc\CMS\Roles;

interface Page {

  public function getPrimaryNavigationNode();

  public function getSlug();

  public function isActive();

  public function setActive($bool);

  //public function addContentStream()
  
}
?>