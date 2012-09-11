<?php

namespace Psc\Form;

use Psc\DateTime\DateTime;

/**
 * @group class:Psc\Form\DateTimeValidatorRule
 */
class DateTimeValidatorRuleTest extends ValidatorRuleTestCase {
  
  protected $rule;
  
  public function setUp() {
    $this->chainClass = 'Psc\Form\DateTimeValidatorRule';
    parent::setUp();
    $this->rule = new DateTimeValidatorRule();
  }
  
  public function provideValidData() {
    $data = function ($date, $time) {
      return array('date'=>$date, 'time'=>$time);
    };
    
    return Array(
      array($data('12.02.2012', '12:12'), DateTime::create('12.02.2012 12:12')),
      array($data('12.02.2012', '12:12'), DateTime::create('12.02.2012 12:12')),
      array($data('29.02.2012', '12:12'), DateTime::create('29.02.2012 12:12')), // schaltjahr
      array($data('28.02.1970', '00:01'), DateTime::create('28.02.1970 00:01')), // 1970 war kein schaltjahr
      array(time(), DateTime::now())
    );
  }

  public function provideEmptyData() {
    $data = function ($date, $time) {
      return array('date'=>$date, 'time'=>$time);
    };
    
    return Array(
      array(array('date'=>NULL, 'time'=>NULL)),
      array($data(NULL, '12:12')), // fehlendes Datum
      array($data('3.09.2013', NULL)), // fehlende Zeit
    );
  }

  public function provideInvalidData() {
    $data = function ($date, $time) {
      return array('date'=>$date, 'time'=>$time);
    };
    
    return Array(
      array($data('29.02.2011', '12:12'), 'Psc\DateTime\ParsingException'), // kein schaltjahr
      array($data('29.02.2011', '00:61'), 'Psc\DateTime\ParsingException') // quatsch zeit
    );
  }
}
?>