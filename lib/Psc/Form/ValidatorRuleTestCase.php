<?php

namespace Psc\Form;

abstract class ValidatorRuleTestCase extends \Psc\Code\Test\Base {
  
  const UNDEFINED = '.::defaultValueIsUndefined::.';
  
  protected $rule;
  
  /**
   * If this is set the defaultValue from the empty Exception is tested
   */
  protected $emptyDefaultValue = self::UNDEFINED;
  
  public function setUp() {
    parent::setUp();
    $this->rule = $this->createRule();
  }
  
  /**
   * @dataProvider provideValidData
   */
  public function testSuccess($data, $expectedReturn = NULL, $rule = NULL) {
    $rule = $rule ?: $this->rule;
    $return = $rule->validate($data);
    
    if (func_num_args() > 1) {
      $this->assertEquals($expectedReturn, $return);
    }
    
    return $return;
  }
  
  /**
   * array($data, $expectedReturn = NULL, $specialRule=NULL)
   */
  abstract public function provideValidData();
  
  /**
   * @dataProvider provideInvalidData
   */
  public function testFailure($data, $exception = NULL, $rule = NULL) {
    $exception = $exception ?: 'Exception';
    $rule = $rule ?: $this->rule;
    
    $this->setExpectedException($exception);
    $rule->validate($data);
  }
  
  /**
   * array($data, $expectedException = 'Exception', $specialRule = NULL)
   */
  abstract public function provideInvalidData();
  
  /**
   * @dataProvider provideEmptyData
   */
  public function testEmpty($data, $exception = 'Psc\Form\EmptyDataException', $rule = NULL) {
    $rule = $rule ?: $this->rule;
    
    
    $e = $this->assertException($exception, function () use ($data, $rule) {
      $rule->validate($data);
    });
    
    if ($this->emptyDefaultValue !== self::UNDEFINED) {
      $this->assertEquals($this->emptyDefaultValue, $e->getDefaultValue(), 'the default value from empty exception does not match');
    }
  }
  
  /**
   * array($data, $expectedException = 'Psc\Form\EmptyDataException', $specialRule = NULL)
   */
  abstract public function provideEmptyData();
  
  protected function createRule() {
    $c = $this->chainClass;
    return new $c();
  }
}
?>