<?php

namespace Psc\CMS\Roles;

interface Page {

  public function getPrimaryNavigationNode();

  public function getSlug();

  public function isActive();

  public function setActive($bool);

  //public function addContentStream()

  public function getContentStreamsByLocale($revision = 'default');

  public function getContentStreamsByRevision($revision = 'default');

  public function getContentStreamByLocale($locale, $revision = 'default');
  
}
?>