<?php

namespace Psc\Code\AST;

use Psc\Code\Generate\ClosureCompiler;
use Psc\Code\Generate\GClass;
use Webforge\Types\Inferrer;

/**
 * 
 */
class DSL extends \Psc\SimpleObject {
  
  private $closures;
  
  /**
   * @var Webforge\Types\Inferrer
   */
  protected $typeInferrer;
  
  public function __construct(Inferrer $typeInferrer = NULL) {
    $this->setTypeInferrer($typeInferrer ?: new Inferrer);
  }
  
  public function parameter($name, $type = NULL) {
    if ($name instanceof LParameter) {
      return $name;
    }
    
    if ($type === NULL) {
      $type = $this->type('Mixed');
    }
    
    return new LParameter($name, $this->type($type));
  }
  
  /**
   * @return LParameters
   */
  public function parameters() {
    $args = func_get_args();
    
    $params = array();
    foreach ($args as $key=>$arg) {
      if (is_string($arg)) {
        $params[] = $this->parameter($arg);
      } elseif ($arg instanceof LParameter) {
        $params[] = $arg;
      } else {
        throw $this->invalidArgument($key+1, $arg, array('String','Object<LParameter>'), __FUNCTION__);
      }
    }
    
    return new LParameters($params);
  }
  
  /**
   * @cc-alias var
   * @return LFunction
   */
  public function function_($name, LParameters $parameters, LStatements $body = NULL) {
    if (!is_string($name))
      throw $this->invalidArgument(1, __FUNCTION__, $name, 'String');
      
    return new LFunction($name, $parameters, $body);
  }
  
  /**
   * @return LStatements
   */
  public function statements() {
    $statements = func_get_args();
    return new LStatements($statements);
  }
  
  /**
   * @return LConstructExpression
   */
  public function construct($class, LArguments $arguments = NULL) {
    return new LConstructExpression($this->className($class), $arguments ?: new LArguments(array()));
  }
  
  /**
   * @return LArguments
   */
  public function arguments() {
    $arguments = func_get_args();
    return new LArguments($arguments);
  }
  
  /**
   * @return LArgument
   */
  public function argument($binding) {
    return new LArgument($this->value($binding));
  }
  
  /**
   * @return LType
   */
  public function type($name) {
    if ($name instanceof LType) {
      return $name;
    }
    
    return new LType($name);
  }
  
  /**
   * @cc-alias var
   */
  public function var_($name, $type = NULL, $initializer = NULL) {
    return new LVariableDefinition($this->variable($name, $type), $this->value($initializer));
  }
  
  /**
   * @param LType $type forced Type für den InferredType
   * @return LValue|LExpression
   */
  public function value($phpValue, $type = NULL) {
    if ($phpValue instanceof LValue) {
      return $phpValue;
    } elseif ($phpValue instanceof LExpression) {
      return $phpValue;
    }
    
    if (!isset($type)) {
      $type = $this->typeInferrer->inferType($phpValue);
    }
    
    return new LValue($phpValue, $this->type($type));
  }
  
  public function expression($string) {
    return new LExpression($string);
  }
  
  /**
   * Gibt eine Expression zurück die in einem Constructor als Klassenname werden kann
   * 
   * @param string $class (später mal mehr)
   */
  public function className($class) {
    if ($class instanceof LClassName) {
      return $class;
    }
    
    if (is_string($class)) {
      return new LClassName($class);
    }
    
    throw $this->invalidArgument(1, __FUNCTION__, $class, 'String');
  }
  
  /**
   * @return hashMap Type
   */
  public function hashMap($traversable) {
    return $this->value((object) $traversable, $this->type('Object<stdClass>'));
  }
  
  /**
   * @return LVariable
   */
  public function variable($name, $type = NULL) {
    if ($name instanceof LVariable) {
      return $name;
    }
    
    if (!isset($type)) {
      $type = 'Mixed';
    }
    
    return new LVariable($name, $this->type($type));
  }
  
  /**
   * Gibt für alle Methoden der Klasse eine Closure zurück
   * 
   * extract($dsl->getClosures());
   * 
   * - $parameter defined
   * - $type defined
   * - ... usw
   * 
   * @return array (für extract geeignet)
   */
  public function getClosures() {
    if (!isset($this->closures)) {
      $cc = new ClosureCompiler();
    
      list($closureCode, $methods) = $cc->compile(new GClass(\Psc\Code\Code::getClass($this)));
      unset($methods[array_search('getClosures', $methods)]);
    
      $that = $this;
      eval($closureCode);
      
      $this->closures = compact($methods);
    }
    
    return $this->closures;
  }
  
  /**
   * @param Webforge\Types\Inferrer $typeInferrer
   */
  public function setTypeInferrer(Inferrer $typeInferrer) {
    $this->typeInferrer = $typeInferrer;
    return $this;
  }
  
  /**
   * @return Webforge\Types\Inferrer
   */
  public function getTypeInferrer() {
    return $this->typeInferrer;
  }
}
