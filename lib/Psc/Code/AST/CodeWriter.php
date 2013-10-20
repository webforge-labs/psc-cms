<?php

namespace Psc\Code\AST;

use Webforge\Types\Type;
use stdClass;

interface CodeWriter {
  
  /**
   * @param string $name
   * @param mixed $walkedValue bereits umgewandelt
   */
  public function writeVariableDefinition($name, $walkedValue);
  
  /**
   * @param mixed $value umgewandelt oder unbekannt
   */
  public function writeValue($value, Type $type);
  
  public function writeVariable($name);
  
  /**
   * @param string $code bereits umgewandelter Code
   */
  public function writeStatement($code);

  /**
   * @param string $code bereits umgewandelter Code
   */
  public function writeExpression($code);

  /**
   * @param string $code bereits umgewandelter Code
   */
  public function writeConstructExpression($className, $argumentsCode);
  
  /**
   * Schreibt einen KlassenNamen z.B. in einer Construct-Expression
   */
  public function writeClassName($name);
  
  // noop
  public function writeArgument($walkedValue);
  
  public function writeArguments(Array $arguments);
  
  /**
   * Eine HashMap hat signifikante Schlüssel (integer oder string)
   *
   * z.B. in Javascript {} (ein Object-Literal)
   */
  public function writeHashMap(stdClass $walkedHashMap, Type $type);
  
  /**
   * Eine Liste ist wie eine HashMap jedoch sind die Schlüssel unrelevant, nur die Reihenfolge
   *
   * z. B. in JavaScript [] (ein Array-Literal)
   */
  public function writeList(Array $walkedList);

}
