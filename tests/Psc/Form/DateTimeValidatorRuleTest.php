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
    
    $optionalTimeRule = new DateTimeValidatorRule($timeIsOptional = TRUE);
    
    return Array(
      array($data('12.02.2012', '12:12'), DateTime::create('12.02.2012 12:12')),
      array($data('12.02.2012', '12:12'), DateTime::create('12.02.2012 12:12')),
      array($data('29.02.2012', '12:12'), DateTime::create('29.02.2012 12:12')), // schaltjahr
      array($data('28.02.1970', '00:01'), DateTime::create('28.02.1970 00:01')), // 1970 war kein schaltjahr

      array($data('12.02.2012', NULL), DateTime::create('12.02.2012 00:00'), $optionalTimeRule),
      array($data('12.02.2012', NULL), DateTime::create('12.02.2012 00:00'), $optionalTimeRule),
      array($data('29.02.2012', ''), DateTime::create('29.02.2012 00:00'), $optionalTimeRule), // schaltjahr
      array($data('28.02.1970', NULL), DateTime::create('28.02.1970 00:00'), $optionalTimeRule), // 1970 war kein schaltjahr
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
      array($data('29.02.2011', '12:12'), 'Webforge\Common\DateTime\ParsingException'), // kein schaltjahr
      array($data('29.02.2011', '00:61'), 'Webforge\Common\DateTime\ParsingException') // quatsch zeit
    );
  }
}
?>