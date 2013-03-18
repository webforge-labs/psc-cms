<?php

namespace Psc\CMS\Roles;

interface SimpleContainer extends \Psc\CMS\Controller\LanguageAware {

  public function setRevision($revision);

  public function getRevision();

  /**
   * Given a shortname like "Page" this function returns the full qualified name of the entity in the project
   */
  public function getRoleFQN($roleName);

}
?>