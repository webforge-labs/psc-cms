<?php

namespace Psc\CMS\Controller;

interface TransactionalController {
  
  /**
   * @return bool
   */
  public function hasActiveTransaction();

  public function beginTransaction();
  
  public function commitTransaction();
  
  public function rollbackTransaction();
}
?>