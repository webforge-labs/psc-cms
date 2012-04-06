<?php

namespace Psc\Data\Ical;

use Psc\Data\Ical\Base;
use Psc\DateTime\DateTime;
use Psc\DateTime\Date;
use Psc\DateTime\DateInterval;

class BaseTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Data\Ical\Base';
    parent::setUp();
    $this->base = $this->createBase();
  }

  /**
   * @dataProvider provideFolding
   */
  public function testFolding($expectedFolded, $value) {
    $this->assertEquals($expectedFolded, $this->base->foldValue($value));
  }
  
  public static function provideFolding() {
    $tests = array();
    
    $test = function ($value, $expectedFolded) use (&$tests) {
      $tests[] = array($expectedFolded, $value);
    };
    
    $test(str_repeat('1',74).str_repeat('2',74),
          str_repeat('1',74)."\n".' '.str_repeat('2',74)
          );
    
    $test(
'X-GOOGLE-EDITURL:https://www.google.com/calendar/feeds/wurstschatulle%40googlemail.com/private/full/8ltjjaaoqc0lgo65c8kuenoof8/63463981899',
'X-GOOGLE-EDITURL:https://www.google.com/calendar/feeds/wurstschatulle%40go
 oglemail.com/private/full/8ltjjaaoqc0lgo65c8kuenoof8/63463981899'
    );
    
    return $tests;
  }
  
  public function testDateTime() {
    $this->assertEquals('20120204T102021Z', $this->base->icalDateTime($dt = new DateTime('4.2.2012 10:20:21')));
  }

  public function testDate() {
    $this->assertEquals('20120204', $this->base->icalDate($dt = new Date('4.2.2012')));
  }
  
  public function testParams() {
    $cr = "\r\n";
    $this->assertEquals('DTEND;VALUE=DATE:20121209'.$cr, $this->base->contentLine('DTEND', '20121209', array('VALUE'=>'DATE')));
    
    $this->assertEquals('DTEND;VALUE=DATE;SENSE=FALSE:20121209'.$cr, $this->base->contentLine('DTEND', '20121209', array('VALUE'=>'DATE','SENSE'=>'FALSE')));
    
    $this->assertEquals('DTEND;VALUE=DATE;MULTI=M1,M2:20121209'.$cr, $this->base->contentLine('DTEND', '20121209', array('VALUE'=>'DATE','MULTI'=>array('M1','M2'))));
  }
  
  public function testIcalRecurrence() {
    $this->assertEquals($this->base->contentLine('RRULE', 'FREQ=YEARLY'), $this->base->icalRecurrence(DateInterval::create('1 YEAR')));
  }

  public function createBase() {
    return new Base();
  }
}
?>