<?php

namespace Psc\Form;

class PasswordValidatorRuleTest extends ValidatorRuleTestCase {
  
  protected $passwordValidatorRule;
  
  public function setUp() {
    $this->chainClass = 'Psc\Form\PasswordValidatorRule';
    parent::setUp();
  }
  
  public function provideInvalidData() {
    $data = function ($password, $confirmation) {
      return array('password'=>$password, 'confirmation'=>$confirmation);
    };
    $ex = function ($exception) {
      return 'Psc\Form\\Password'.$exception.'Exception';
    };
    
    return Array(
      // user gimbel
      array($data('shrt','shrt'),$ex('MinLength')),
      array($data('longBut','notEqual'),$ex('Confirmation')),
      array($data('longBut',NULL),$ex('Confirmation')),
      array($data(NULL,'notenough'),$ex('MinLength')),
      
      // not sure
      array($data(' ',NULL),$ex('MinLength')), // das zu empty data?
      
      // system gimbel
      array(array()),
      array(array('password'=>NULL)),
      array(array('confirmation'=>NULL)),
      array(array('pw'=>NULL)),
    );
  }
  
  public function provideEmptyData() {
    $data = function ($password, $confirmation) {
      return array('password'=>$password, 'confirmation'=>$confirmation);
    };
    
    // leere strings und nicht ausgefühlt sind leer. Jedoch nicht strings mit whitespace
    return Array(
      array($data(NULL,NULL)),
      array($data('',NULL)),
      array($data(NULL,'')),
      array($data('',''))
    );
  }
  
  public function provideValidData() {
    $data = function ($password, $confirmation) {
      return array('password'=>$password, 'confirmation'=>$confirmation);
    };
    
    return Array(
      array($data('longenough','longenough')),
      array($data('123§$)$(I','123§$)$(I')),
    );
  }
  
  protected function createRule() {
    return new PasswordValidatorRule(5);
  }
}
?>