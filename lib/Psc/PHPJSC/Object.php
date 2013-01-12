<?php

namespace Psc\PHPJSC;

interface Object extends \Psc\PHPJS\Object {
  
  /**
   * @return array die Schlüssel sind Funktionsnamen. Der Wert sind Flags für CompilerObject::compileJS()
   */
  public function getJSMethods();
  
  public function isPHP();
  public function isJS();
  
  public function &refThis($variable);
  
  public function compileJS();
  
}