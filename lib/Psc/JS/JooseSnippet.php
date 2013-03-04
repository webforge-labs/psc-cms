<?php

namespace Psc\JS;

use stdClass;
use Webforge\Common\String AS S;
use Psc\HTML\Tag;

/**
 * 
 */
class JooseSnippet extends Snippet implements \Psc\Code\AST\Walkable {
  
  /**
   * @var string
   */
  protected $class;
  
  /**
   * @var stdClass
   */
  protected $constructParams;
  
  /**
   * 
   * durch den ersten parameter von getAST() erbt das Snippet das nestLevel+1 vom "parent" snippet. Dann ist diese Variable unrelevant
   * @var integer
   */
  protected $nestLevel;
  
  public function __construct($jooseClass, stdClass $constructParams = NULL, Array $dependencies = array(), $nestLevel = 0) {
    $this->class = $jooseClass;
    $this->constructParams = (object) $constructParams;
    $this->setUse(array_merge(array($this->class), $dependencies));
    parent::__construct(NULL); // erstmal empty
    $this->setNestLevel($nestLevel);
    $this->loadOnPscReady(TRUE);
  }
  
  /**
   * @param array|object $constructParams
   */
  public static function create($jooseClass, $constructParams = NULL, Array $dependencies = array(), $nestLevel = 0) {
    return new static($jooseClass, (object) $constructParams, $dependencies, $nestLevel);
  }
  
  /**
   * Kleiner Helfer für das Erzeugen einer Expression
   *
   * JooseSnippet::expr("$('#psc-ui-tabs')")
   */
  public static function expr($jsCode) {
    $dsl = new \Psc\Code\AST\DSL();
    return $dsl->expression($jsCode);
  }
  
  public function help() {
    $dsl = new \Psc\Code\AST\DSL();
    return $dsl->getClosures();
  }
  
  
  public function getAST($nestLevel = NULL) {
    if ($nestLevel === NULL) $nestLevel = $this->nestLevel;
    
    // hier closures zu nehmen von help() ist sehr sehr sehr sehr langsam
    $dsl = new \Psc\Code\AST\DSL();
    
    $cArguments = new stdClass;
    foreach ($this->constructParams as $name => $value) {
      if ($value instanceof JooseSnippet) {
        $cArguments->$name = $value->getAST($nestLevel+1);
        $this->addUse($value->getUse());
      } else {
        $cArguments->$name = $value;
      }
    }
    
    $constructAST = $dsl->construct($this->class, $dsl->arguments($dsl->argument($cArguments)));
    
    if ($nestLevel == 0) {
      return $dsl->var_('j', NULL, $constructAST);
    } else {
      return $constructAST;
    }
  }

  public function getWalkableAST() {
    return $this->getAST($nestLevel = 1); // mindestens 1, damit wir nicht so tun als wären wir standalone
  }
  
  public function getCode() {
    $walker = new \Psc\Code\AST\Walker(new \Psc\JS\AST\CodeWriter());
    
    return $walker->walkElement($this->getAST());
  }
  
  /**
   * @return string
   */
  public function getClass() {
    return $this->class;
  }
  
  /**
   * @param string $class
   */
  public function setClass($class) {
    $this->class = $class;
    return $this;
  }
  
  /**
   * @param stdClass $constructParams
   */
  public function setConstructParams(stdClass $constructParams) {
    $this->constructParams = $constructParams;
    return $this;
  }
  
  /**
   * @return stdClass
   */
  public function getConstructParams() {
    return $this->constructParams;
  }
  
  /**
   * @param integer $nestLevel
   */
  public function setNestLevel($nestLevel) {
    $this->nestLevel = $nestLevel;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getNestLevel() {
    return $this->nestLevel;
  }
}
?>