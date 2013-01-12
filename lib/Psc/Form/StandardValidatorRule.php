<?php

namespace Psc\Form;

use Psc\String AS S;
use Psc\Code\Code;
use Psc\Code\Callback;

class StandardValidatorRule implements ValidatorRule {
  
  /**
   * 
   * 1. Parameter sind die Daten die validiert werden sollen
   * 
   * @var Psc\Code\Callback
   */
  protected $callback;
  
  /**
   * Erstellt eine neue Rule durch eine Spezifikation (was so quasi alles sein kann)
   *
   * Die Parameter dieser Funktion sind sehr Variabel
   * Möglicherweise ist es einfacher generate[A-Za-z+]Rule zu benutzen
   */
  public static function generateRule($ruleSpecification) {
    $rs = $ruleSpecification;
    
    if (is_string($rs)) {
      
      if ($rs == 'nes') {
        return new NesValidatorRule();
      }
      
      if ($rs == 'id') {
        return new IdValidatorRule();
      }
      
      if ($rs == 'pi' || $rs == 'pint') {
        return new PositiveIntValidatorRule();
      }

      if ($rs == 'array') {
        return new ArrayValidatorRule();
      }
      
      if ($rs == 'pi0' || $rs == 'pint0') {
        $rule = new PositiveIntValidatorRule();
        $rule->setZeroAllowed(TRUE);
        return $rule;
      }
      
      if (S::startsWith($rs,'/') && S::endsWith($rs,'/'))
        return self::generateRegexpRule($rs);
        
      $c = '\\'.__NAMESPACE__.'\\'.ucfirst($rs).'ValidatorRule';
      if (class_exists($c)) {
        return new $c;
      }
        
      throw new \Psc\Exception('Unbekannte Parameter für generateRule '.Code::varInfo($rs));
    }
    
    if (is_array($rs)) {
      $num = count($rs);
      
    }
    
    throw new \Psc\Exception('Unbekannte Parameter für generateRule '.Code::varInfo($rs));
  }
  
  /**
   * Das Pattern muss einfach nur Matchen
   */
  public static function generateRegexpRule($pattern) {
    
  }
  

  /**
   * Validiert die gespeicherte Rule
   * 
   * Achtung! nicht bool zurückgeben, stattdessen irgendeine Exception schmeissen
   * @return $data
   */
  public function validate($data) {
    if (isset($this->callback)) {
      return $this->callback->call(array($data));
    }
    
    throw new \Psc\Exception('empty rule!');
  }
  
  public function setCallback(Callback $callback) {
    $this->callback = $callback;
    return $this;
  }  
}
?>