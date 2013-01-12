<?php

namespace Psc\CMS;

/**
 * Ein DefaultRequestableEntity entscheidet seine Default Action selbst
 */
interface DefaultRequestableEntity {
  
  /**
   * Gibt die RequestMeta zurück, die benutzt werden soll, wenn kein bestimter Kontext besteht
   *
   * dies ist also in 99% der Fälle die Action im Tab für das Entity geöffnet wird
   */
  public function getDefaultRequestMeta(EntityMeta $entityMeta);
}
?>