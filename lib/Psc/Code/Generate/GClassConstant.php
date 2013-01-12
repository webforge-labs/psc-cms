<?php

namespace Psc\Code\Generate;

use Reflector;

/**
 * 
 */
class GClassConstant extends GObject {

  /**
   * @var string
   */
  protected $name;

  /**
   * @var mixed
   */
  protected $value;
  
  /**
   * @var Psc\Code\Generate\GClass
   */
  protected $gClass;
  
  public function __construct($name, GClass $gClass, $value) {
    $this->setGClass($gClass);
    $this->setValue($value);
    $this->setName($name);
  }

  /**
   * Gibt den PHP Code für die Konstante zurück
   *
   */
  public function php($baseIndent = 0) {
    $php = $this->phpDocBlock($baseIndent);
    
    $php .= str_repeat(' ',$baseIndent);
    
    $php .= 'const '.$this->name;
    $php .= ' = '.$this->exportPropertyValue($this->getValue());
    
    return $php;
  }
  
  /**
   * @param Psc\Code\Generate\GClass $gClass
   */
  public function setGClass(GClass $gClass) {
    $this->gClass = $gClass;
    return $this;
  }
  
  /**
   * @return Psc\Code\Generate\GClass
   */
  public function getGClass() {
    return $this->gClass;
  }
  
  /**
   * @param mixed $value
   */
  public function setValue($value) {
    $this->value = $value;
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function getValue() {
    return $this->value;
  }
  
  /**
   * @param string $name
   */
  public function setName($name) {
    $this->name = mb_strtoupper($name);
    return $this;
  }
  
  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }
  
  public function elevate(Reflector $reflector) {
    throw new \Psc\Exception('ClassConstant hat keine Reflector zum elevaten');
  }
}
?>