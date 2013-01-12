<?php

namespace Psc\Data\Type;

/**
 * The type can be hinted for paremetes in a function or method
 *
 * ArrayType is a special ParameterHintedType because it has not a class as hint
 */
interface ParameterHintedType {
  
  /**
   * @return string
   */
  public function getParameterHint($useFQN = TRUE);
  
  
  /**
   * @return Psc\Code\Generate\GClass
   */
  public function getParameterHintImport();

}
?>