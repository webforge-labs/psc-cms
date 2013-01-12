<?php

namespace Psc\JS\AST;

use Psc\Data\Type\Type;
use Psc\JS\Helper as jsHelper;
use Psc\Code\AST\CodeWriter as ASTCodeWriter;
use stdClass;
use Psc\String as S;

class CodeWriter extends \Psc\SimpleObject implements ASTCodeWriter {
  
  protected $indent = 0;
  
  protected function p($code) {
    return $this->i().$code."\n";
  }
  
  protected function i() {
    return str_repeat('  ', $this->indent);
  }
  
  protected function indent($code, $relative = 0) {
    return S::indent($code, ($relative+$this->indent)*2);
  }
  
  /**
   * @param string $name
   * @param mixed $walkedValue bereits umgewandelt
   */
  public function writeVariableDefinition($name, $walkedValue) {
    return sprintf('%svar %s = %s', $this->i(), $name, $walkedValue);
  }
  
  /**
   * @param mixed $value nicht umgewandelt
   * z.B. NULL
   */
  public function writeValue($value, Type $type) {
    if ($value === NULL)
      return 'undefined';
    
    return jsHelper::convertValue($value);
  }
  
  public function writeVariable($name) {
    return $name;
  }
  
  /**
   * @param string $code bereits umgewandelter Code
   */
  public function writeStatement($code) {
    return $this->p($code.';');
  }

  /**
   * @param string $code bereits umgewandelter Code
   */
  public function writeExpression($code) {
    return $code;
  }

  public function writeConstructExpression($className, $argumentsCode) {
    return sprintf('new %s(%s)', $className, $this->indent($argumentsCode, +1));
  }
  
  /**
   * Schreibt einen KlassenNamen z.B. in einer Construct-Expression
   */
  public function writeClassName($name) {
    return $name;
  }

  // noop
  public function writeArgument($walkedValue) {
    return $walkedValue;
  }

  public function writeArguments(Array $arguments) {
    return implode(", ", $arguments);
  }

  /**
   * Eine HashMap hat signifikante Schlüssel (integer oder string)
   *
   * z.B. in Javascript {} (ein Object-Literal)
   * nach der schließen klammer } ist kein umbruch, für maximale flexibilität
   */
  public function writeHashMap(stdClass $walkedHashMap, Type $type) {
    $hashMap = '{';
    foreach ($walkedHashMap as $field => $walkedValue) {
      $hashMap .= sprintf("'%s': %s, ", $field, $walkedValue);
    }
    $hashMap = mb_substr($hashMap, 0, -2);
    $hashMap .= '}';
    return $hashMap;
  }
  
  /**
   * Eine Liste ist wie eine HashMap jedoch sind die Schlüssel unrelevant, nur die Reihenfolge
   *
   * z. B. in JavaScript [] (ein Array-Literal)
   */
  public function writeList(Array $walkedList) {
    return '['.implode(', ', $walkedList).']';
  }
}
?>