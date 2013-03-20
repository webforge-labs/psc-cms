<?php

namespace Psc\Entities;

use stdClass;

class NavigationNodeRepository extends \Psc\Doctrine\NavigationNodeRepository {

  protected function createNewNode(stdClass $jsonNode) {
    return new NavigationNode($jsonNode->title);
  }
}
?>