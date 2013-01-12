<?php

namespace Psc\Data;

/**
 * ValueObject für die Größe eines Objektes
 *
 * wird standardmäßig in breite x höhe (immer breite zuerst) und in ganzzahlen angegeben (wird jedoch nicht gecheckt, somit sind floats auch benutzbar)
 */
class Dimension extends ValueObject {
  
  /**
   * @var integer
   */
  protected $width;
  
  /**
   * @var integer
   */
  protected $height;
  
  public function __construct($width, $height) {
    $this->setWidth($width);
    $this->setHeight($height);
  }

  /**
   * @return stdClass .width .height (im Default)
   */
  public function export() {
    return (object) array('width'=>$this->width, 'height'=>$this->height);
  }
  
  /**
   * @param integer $width
   */
  public function setWidth($width) {
    $this->width = $width;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getWidth() {
    return $this->width;
  }
  
  /**
   * @param integer $height
   */
  public function setHeight($height) {
    $this->height = $height;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getHeight() {
    return $this->height;
  }
}
?>