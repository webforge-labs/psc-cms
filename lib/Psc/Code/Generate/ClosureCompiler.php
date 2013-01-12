<?php

namespace Psc\Code\Generate;

/**
 * Compiled dynamische Closures für public Methoden einer Klasse
 *
 * Wenn Namen für die Closures vergeben werden sollen, die anders lauten als der methodenName kann in den DocBlock der Methode
 * @cc-alias derClosureName
 *
 * eingefügt werden. (cc für closure compiler)
 *
 * @see ClosureCompilerTest
 * 
 */
class ClosureCompiler extends \Psc\SimpleObject {
  
  /**
   * Indiziert nach Closure Name (also der Alias)
   */
  protected $methods = array();
  
  /**
   * @return list($phpCode, $methodNames)
   */
  public function compile(GClass $gClass) {
    $this->methods = array();
    $gClass->elevateClass();
    
    $closures = array();
    $methodNames = array();
    foreach ($gClass->getAllMethods() as $method) {
      $closureName = $method->getName();
      
      if ($method->hasDocBlock()) {
        $docBlock = $method->getDocBlock();
        
        if (($ccAlias = \Psc\Preg::qmatch($docBlock->toString(), '/@cc-alias\s+(.*?)([\s]|$)/im', 1)) !== NULL) {
          $closureName = $ccAlias;
        }
        
        if (\Psc\Preg::match($docBlock->toString(), '/@cc-ignore/i')) {
          continue;
        }
      }
      
      $closures[$closureName] = $this->compileClosure($method, $closureName);
      $this->methods[$closureName] = $method;
      $methodNames[] = $closureName;
    }
    
    return array(\Psc\A::join($closures, "%s;\n\n"), $methodNames);
  }
  
  protected function compileClosure(GMethod $method, $closureName) {
    // wir erzeugen sowas hier (ohne semikolon am ende)
    /*
      parameter von $funcName sind $name und $type
     
      $funcName = function ($name, $type) use ($that) {
        return $that->funcName($name, $type);
      }
    */
    $code = <<<'PHP'
$%closureName% = function (%parameters%) use ($that) {
  return $that->%method%(%callParameters%);
}
PHP;

    $codeWithFuncArgs = <<<'PHP'
$%closureName% = function () use ($that) {
  return call_user_func_array(array($that, '%method%'), func_get_args());
}
PHP;

    // wenn die innere funktion func_get_args() benutzt, müssen wir call_user_func nehmen ($codeWithFuncArgs)
    if (mb_strpos($method->getBody(), 'func_get_args()')) {// das ist natürlich nicht so schön als entscheidung, aber false positive macht nichts (außer bissl performance)
      $code = $codeWithFuncArgs;
      $parameters = $callParameters = NULL;
    } else {

      // kopiert von GFunction
      $parameters = \Psc\A::implode($method->getParameters(), ', ', function ($parameter) {
        return $parameter->php($useFQNHints = TRUE);
      });
    
      $callParameters = \Psc\A::implode($method->getParameters(), ', ', function ($parameter) {
        // für die 2ten parameter entfernen wir alle hints, damit wir nur die parameter auflistungen haben
        $parameter = clone $parameter;
        $parameter->setHint(NULL);
        $parameter->setArray(FALSE);
        $parameter->setDefault(GParameter::UNDEFINED);

        return $parameter->php();
      });
    }
    
    $code = \Psc\TPL\TPL::miniTemplate($code, array(
      'parameters'=>$parameters,
      'callParameters'=>$callParameters,
      'method'=>$method->getName(),
      'closureName'=>$closureName
    ));

    return $code;
  }
  
  /**
   * @return array indiziert nach ClosureName (also alias applied)
   */
  public function getMethods() {
    return $this->methods;
  }
  
  /**
   * @return DocBlock[] indiziert bei closureName
   */
  public function getDocumentation() {
    $docBlocks = array();
    foreach ($this->methods as $closureName => $method) {
      if ($method->hasDocBlock()) {
        $docBlocks[$closureName] = $method->getDocBlock();
      } else {
        $docBlocks[$closureName] = new \Psc\Code\Generate\DocBlock('missing documentation');
      }
    }
    return $docBlocks;
  }
}
?>