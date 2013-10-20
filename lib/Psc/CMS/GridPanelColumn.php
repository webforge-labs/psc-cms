<?php

namespace Psc\CMS;

use Webforge\Types\Type;
use Closure;

/**
 * 
 */
class GridPanelColumn extends \Psc\Data\Column {
  
  /**
   * @var array
   */
  protected $classes = array();
  
  /**
   * @var Closure
   */
  protected $converter;
  
  public function __construct($name, Type $type, $label, Array $classes = array(), Closure $converter = NULL) {
    $this->setClasses($classes);
    parent::__construct($name, $type, $label);
    if ($converter)
      $this->setConverter($converter);
  }
  
  /**
   * @param array $classes
   */
  public function setClasses(Array $classes) {
    $this->classes = $classes;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getClasses() {
    return $this->classes;
  }
  
  /**
   * @param string $class
   * @chainable
   */
  public function addClass($class) {
    $this->classes[] = $class;
    return $this;
  }
  
  /**
   * @param string $class
   * @chainable
   */
  public function removeClass($class) {
    if (($key = array_search($class, $this->classes, TRUE)) !== FALSE) {
      array_splice($this->classes, $key, 1);
    }
    return $this;
  }
  
  /**
   * @param string $class
   * @return bool
   */
  public function hasClass($class) {
    return in_array($class, $this->classes);
  }
  
  /**
   * @param Closure $converter function($entity, $column)
   */
  public function setConverter(Closure $converter) {
    $this->converter = $converter;
    return $this;
  }
  
  /**
   * @return Closure
   */
  public function getConverter() {
    return $this->converter;
  }
}
