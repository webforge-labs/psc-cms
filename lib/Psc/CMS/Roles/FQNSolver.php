<?php

namespace Psc\CMS\Roles;

interface FQNSolver {

  /**
   * Given a shortname like "Page" this function returns the full qualified name of the entity in the project
   * 
   * per Convention: CS\\ should Expand to Project\EntitiesNamespace\ContentStream
   * e.g. CS\Headline => CoMun\Entities\ContentStream\Headline
   * @param string $roleName
   * @return string FQN
   */
  public function getRoleFQN($roleName);

}
?>