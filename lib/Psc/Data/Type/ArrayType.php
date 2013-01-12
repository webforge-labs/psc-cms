<?php

namespace Psc\Data\Type;

/**
 * Der Typ für einen Array
 * 
 * ich glaube es ist besser nur eine TypeKlasse für einen Array zu haben und diese hat dann irgendwann sehr viele Parameter
 * - NULLallowed
 * - Type
 * - Dimensions
 * - Types in der Dimension?
 * - mixed Types?
 * - fixedLength
 * 
 * sonst hätten wir: FixedLengthArray, TypedArray, DimensionArray, MixedTypedArray etc etc (das wären ganz schön viele Klassen)
 * und: wir könnten nicht "zwischen den Klassen" hin und herwechseln.
 */
class ArrayType extends Type implements TraversableType, \Psc\Doctrine\ExportableType, ParameterHintedType {
  
  /**
   * Der Typ der Elemente im Array
   * 
   * kann NULL sein, dann ist der Array nicht getyped
   * @var Psc\Data\Type\Type|NULL
   */
  protected $type;
  
  /**
   * @var bool|NULL
   */
  protected $isList = NULL;
  
  public function __construct(Type $type = NULL, $list = NULL) {
    $this->type = $type ?: NULL;
    $this->isList = $list; // not the setter, because it casts to bool. NULL is a valid value for this property
  }
  
  /**
   * @return bool
   */
  public function isTyped() {
    return isset($this->type);
  }
  
  /**
   * @return Psc\Data\Type\Type
   */
  public function getType() {
    if (!isset($this->type)) throw new ArrayNotTypedException('Kann den Type des ArrayType nicht zurückgeben.');
    return $this->type;
  }
  
  /**
   * 
   * wird der Parameter NULL Übergeben ist der Array nicht mehr getyped
   * @param Psc\Data\Type\Type|NULL
   */
  public function setType(Type $type = NULL) {
    $this->type = $type;
    return $this;
  }
  
  public function getDoctrineExportType() {
    return \Doctrine\DBAL\Types\Type::TARRAY;
  }
  
  public function getDefaultValue() {
    return array();
  }
  
  public function hasScalarDefaultValue() {
    return TRUE;
  }
  
  /**
   * @return bool|NULL wenn es === NULL ist, gibt es keine Aussage explizite Aussage darüber ob das Array eine Liste ist
   */
  public function isList() {
    return $this->isList;
  }
  
  /**
   * @param bool|NULL $list
   */
  public function setIsList($list) {
    $this->isList = $list;
    return $this;
  }
  
  /**
   * @inheritdoc
   */
  public function getParameterHint($useFQN = TRUE) {
    return 'Array';
  }
  
  /**
   * @inheritdoc
   */
  public function getParameterHintImport() {
    return NULL;
  }
}
?>