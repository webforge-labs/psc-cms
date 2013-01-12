<?php

namespace Psc\Code;

interface WriteableAnnotation {
  
  /**
   * Gibt eine Key/Value Liste von den Werten zurück die Exportiert/Geschrieben werden sollen
   *
   * das bedeutet, dass Defaults für die Annotation weggelassen werden können (muss aber nicht)
   * @return array
   */
  public function getWriteValues();
}
?>