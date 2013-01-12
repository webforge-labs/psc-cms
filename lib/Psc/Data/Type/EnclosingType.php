<?php

namespace Psc\Data\Type;

/**
 * Ein EnclosingType hat einen "innerType" der mit getType zurückgegeben wird
 *
 * Dabei ist es optional ob der EnclosingType gerade einen Type hat oder nicht.
 * EnclosingTypes werden mit
 *
 * $outerType<$innerType>
 *
 * dokumentiert
 */
interface EnclosingType {

  /**
   * @return bool
   */
  public function isTyped();
  
  /**
   * @return Psc\Data\Type\Type
   * @throws NotTypedException wenn der Type nicht gesetzt ist
   */
  public function getType();
  
  /**
   *
   * wird der Parameter NULL Übergeben ist der Type nicht mehr getyped
   * @param Psc\Data\Type\Type|NULL
   */
  public function setType(Type $type = NULL);
}
?>