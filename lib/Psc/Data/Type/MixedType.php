<?php

namespace Psc\Data\Type;

/**
 * Ein Pseudotype für den DokumentationsTyp "mixed"
 */
class MixedType extends Type implements PseudoType {

  /**
   * Der Namen des Pseudo-Typen für DocBlocks
   */
  public function getDocType() {
    return 'mixed';
  }
}
?>