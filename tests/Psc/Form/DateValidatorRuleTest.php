<?php

namespace Psc\Form;

use Psc\DateTime\Date;

/**
 * @group class:Psc\Form\DateValidatorRule
 */
class DateValidatorRuleTest extends ValidatorRuleTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\Form\DateValidatorRule';
    parent::setUp();
  }
  
  
  /**
   * @expectedException Psc\Code\NotImplementedException
   * @dataProvider provideBadFormats
   */
  public function testBadConstruct($format) {
    new DateValidatorRule($format);
  }

  /**
   * @dataProvider provideFormats
   */
  public function testConstruct($format) {
    $rule = new DateValidatorRule($format);
    $this->assertEquals($format, $rule->getFormat());
  }
  
  public function provideBadFormats() {
    return array(
      array('d.m.y'),
      array('d.m'),
      array('D.m.')
    );
  }

  public function provideFormats() {
    return array(
      array('d.m.Y'),
      array('d.m.')
    );
  }
  
  public function provideValidData() {
    return Array(
      array('12.02.2012', Date::create('12.02.2012'), new DateValidatorRule('d.m.Y')),
      array('12.02.2012', Date::create('12.02.2012'), new DateValidatorRule('d.m.Y')),
      array('29.02.2012', Date::create('29.02.2012'), new DateValidatorRule('d.m.Y')), // schaltjahr
      
      array('29.02.', Date::create('29.02.1972'), new DateValidatorRule('d.m.')), // 1972 war ein schaltjahr
      array('28.02.', Date::create('28.02.1970'), new DateValidatorRule('d.m.')) // 1970 war kein schaltjahr
    );
  }
  
  public function provideEmptyData() {
    return Array(
      array(NULL)
    );
  }
  
  public function provideInvalidData() {
    return Array(
      array('29.02.2011', 'Webforge\Common\DateTime\ParsingException', new DateValidatorRule('d.m.Y')) // kein schaltjahr
      
      
    );
  }
}
?>