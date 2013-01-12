<?php

namespace Psc\Form;

/**
 * @group class:Psc\Form\FloatValidatorRule
 */
class FloatValidatorRuleTest extends  ValidatorRuleTestCase {
  
  protected $floatValidatorRule;
  
  public function setUp() {
    $this->chainClass = 'Psc\Form\FloatValidatorRule';
    parent::setUp();
    
  }
  
  public function provideInvalidData() {
    return Array(
      array('0,00'),
      array('str'),
      array(array()),
      array('12,000,01')
    );
  }

  public function provideValidData() {
    return Array(
      array('0,00', 0.00, new FloatValidatorRule(TRUE)),
      array('0', 0.00, new FloatValidatorRule(TRUE)),
      array('0,01', 0.01),
      array('12', 12.0),
      array('12000,01', 12000.01),
      array('12.000,01', 12000.01)
    );
  }
  
  public function provideEmptyData() {
    return Array(
      array(NULL),
      array('')
    );
  }
  
  protected function createRule() {
    return new FloatValidatorRule(FALSE);
  }
}
?>