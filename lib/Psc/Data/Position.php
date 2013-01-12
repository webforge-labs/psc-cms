<?php

namespace Psc\Data;

/**
 * Ein Value Object für eine Position eines Elements
 * 
 * standardmäßig werden die Koordinaten als ganzzahlen mit top: und left: angegeben
 * (top immer zuerst)
 */
class Position extends \Psc\Data\ValueObject implements \Psc\Data\Exportable {
  
  /**
   * @var integer
   */
  protected $top;
  
  /**
   * @var integer
   */
  protected $left;
  
  public function __construct($top, $left) {
    $this->setTop($top);
    $this->setLeft($left);
  }
  
  /**
   * @return stdClass .top .left (im Default)
   */
  public function export() {
    return (object) array('top'=>$this->top, 'left'=>$this->left);
  }
  
  /**
   * @param integer $top
   */
  public function setTop($top) {
    $this->top = $top;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getTop() {
    return $this->top;
  }
  
  /**
   * @param integer $left
   */
  public function setLeft($left) {
    $this->left = $left;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getLeft() {
    return $this->left;
  }
}
?>