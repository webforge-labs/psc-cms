<?php

namespace Psc\CMS;

interface LabelerAware {
  
  public function setLabeler(\Psc\CMS\Labeler $labeler);
  
  /**
   * @return Psc\CMS\Labeler
   */
  public function getLabeler();
  
}
?>