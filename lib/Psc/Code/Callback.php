<?php

namespace Psc\Code;

use Closure;

/**
 * Klasse für die modellierung des Pseudo-Types "Callback" aus dem PHP-Manual
 * 
 * Ermöglicht dem Callback statische Parameter hinzufügen
 * diese statischen Parameter werden in der Reihenfolge für den Callback aufruf VOR die dynamischen Parameter gehängt
 */
class Callback extends \Psc\SimpleObject implements Callable {
  
  const TYPE_METHOD = 'method';
  const TYPE_STATIC_METHOD = 'static_method';
  const TYPE_FUNCTION = 'function';
  const TYPE_CLOSURE = 'closure';
  
  /**
   * 
   * @var array
   */
  protected $staticParameters;
  
  /**
   *
   * Wenn NULL, muss $function ein String sein und ist damit eine statische Funktion oder ein Closure
   * Wenn string, ist $target ein Klassenname (voll qualifiziert natürlich)
   * Wenn object, ist $target ein Objekt auf dem $function aufgerufen wird
   * Wenn closure, wird $function ignoriert
   * @var string|object|Closure|NULL
   */
  protected $target;
  
  /**
   *
   * ist dies ein String, wird dies als funktion interpretiert
   * @var string
   */
  protected $function;
  
  /**
   * @var const TYPE_
   */
  protected $type;
  
  
  /**
   * @var int
   */
  protected $called = 0;
  
  public function __construct($objectOrClassOrClosure, $function = NULL, Array $staticParameters = array()) {
    $this->target = $objectOrClassOrClosure;
    $this->function = $function;
    $this->staticParameters = $staticParameters;
    
    $this->calculateType();
  }
  
  public function call(Array $arguments = array()) {
    $arguments = array_merge($this->staticParameters, $arguments);
    $this->called++;
    
    if ($this->getType() === self::TYPE_CLOSURE) {
      return $this->callClosure($this->target, $arguments);

    } elseif ($this->getType() === self::TYPE_METHOD) {
      return $this->callObject($this->target, $this->function, $arguments);

    } elseif ($this->getType() === self::TYPE_STATIC_METHOD) {
      return $this->callClass($this->target, $this->function, $arguments);
      
    } elseif ($this->getType() === self::TYPE_FUNCTION) {
      return $this->callFunction($this->function, $arguments);
      
    }
    
    $this->called--;
    throw new \Psc\Exception('Internal Error: fallthrough');
  }
  
  protected function calculateType() {
    if (is_string($this->target) && !empty($this->target) && is_string($this->function)) {
      $this->type = self::TYPE_STATIC_METHOD;
    } elseif (is_object($this->target) && is_string($this->function)) {
      $this->type = self::TYPE_METHOD;
    } elseif ($this->function instanceof Closure) {
      $this->type = self::TYPE_CLOSURE;
      $this->target = $this->function;
      $this->function = NULL;
    } elseif ($this->target instanceof Closure) {
      $this->type = self::TYPE_CLOSURE;
      $this->function = NULL;
    } elseif (is_string($this->function) && empty($this->target)) {
      $this->type = self::TYPE_FUNCTION;
      $this->target = NULL;
    } else {
      throw new \Psc\Exception('Invalide Kombination von Argumenten: target: '.Code::varInfo($this->target).' function: '.Code::varInfo($this->function));
    }
  }
  
  public function getType() {
    return $this->type;
  }
  
  public function wasCalled() {
    return $this->called > 0;
  }
  
  public function getCalled() {
    return $this->called;
  }
  
  protected function callClosure(Closure $f, Array $args) {
    $n = count($args);
    
    if ($n === 0)
      return $f();
    elseif ($n === 1)
      return $f($args[0]);
    elseif ($n === 2)
      return $f($args[0],$args[1]);
    elseif ($n === 3)
      return $f($args[0],$args[1],$args[2]);
    elseif ($n === 4)
      return $f($args[0],$args[1],$args[2],$args[3]);
    elseif ($n === 5)
      return $f($args[0],$args[1],$args[2],$args[3],$args[4]);
    elseif ($n === 6)
      return $f($args[0],$args[1],$args[2],$args[3],$args[4],$args[5]);
    
    return call_user_func_array($f, $args);
  }
  
  protected function callFunction($f, Array $args) {
    $n = count($args);
    
    if ($n === 0)
      return $f();
    elseif ($n === 1)
      return $f($args[0]);
    elseif ($n === 2)
      return $f($args[0],$args[1]);
    elseif ($n === 3)
      return $f($args[0],$args[1],$args[2]);
    elseif ($n === 4)
      return $f($args[0],$args[1],$args[2],$args[3]);
    elseif ($n === 5)
      return $f($args[0],$args[1],$args[2],$args[3],$args[4]);
    elseif ($n === 6)
      return $f($args[0],$args[1],$args[2],$args[3],$args[4],$args[5]);
    
    return call_user_func_array($f, $args);
  }

  protected function callClass($c, $f, Array $args) {
    $n = count($args);
    
    if ($n === 0)
      return $c::$f();
    elseif ($n === 1)
      return $c::$f($args[0]);
    elseif ($n === 2)
      return $c::$f($args[0],$args[1]);
    elseif ($n === 3)
      return $c::$f($args[0],$args[1],$args[2]);
    elseif ($n === 4)
      return $c::$f($args[0],$args[1],$args[2],$args[3]);
    elseif ($n === 5)
      return $c::$f($args[0],$args[1],$args[2],$args[3],$args[4]);
    elseif ($n === 6)
      return $c::$f($args[0],$args[1],$args[2],$args[3],$args[4],$args[5]);
    
    return call_user_func_array(array($c,$f), $args);
  }

  protected function callObject($o, $f, Array $args) {
    $n = count($args);
    
    if ($n === 0)
      return $o->$f();
    elseif ($n === 1)
      return $o->$f($args[0]);
    elseif ($n === 2)
      return $o->$f($args[0],$args[1]);
    elseif ($n === 3)
      return $o->$f($args[0],$args[1],$args[2]);
    elseif ($n === 4)
      return $o->$f($args[0],$args[1],$args[2],$args[3]);
    elseif ($n === 5)
      return $o->$f($args[0],$args[1],$args[2],$args[3],$args[4]);
    elseif ($n === 6)
      return $o->$f($args[0],$args[1],$args[2],$args[3],$args[4],$args[5]);
    
    return call_user_func_array(array($o,$f), $args);
  }
  
  public static function create($objectOrClassOrClosure, $function = NULL, Array $staticParameters = array()) {
    return new static($objectOrClassOrClosure, $function, $staticParameters);
  }
}
?>