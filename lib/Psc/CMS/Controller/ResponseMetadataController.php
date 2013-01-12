<?php

namespace Psc\CMS\Controller;

interface ResponseMetadataController {
  
  /**
   * Den MetadataGenerator für die Ausgabe benutzen
   * 
   * @return Psc\CMS\Service\MetadataGenerator
   */
  public function getResponseMetadata();
}
?>