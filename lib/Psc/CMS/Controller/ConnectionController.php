<?php

namespace Psc\CMS\Controller;

interface ConnectionController {
  
  /**
   * @return Doctrine\DBAL\Connection
   */
  public function getConnection();
  
}
?>