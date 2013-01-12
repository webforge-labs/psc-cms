<?php

namespace Psc\Doctrine;

interface ExportableType {

  /**
   * Gibt den String zurück, der in @Doctrine\ORM\Mapping\Column(type="%s")  benutzt werden kann
   */
  public function getDoctrineExportType();
  
}
?>