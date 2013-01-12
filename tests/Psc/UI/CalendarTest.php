<?php

namespace Psc\UI;

/**
 * @group class:Psc\UI\Calendar
 */
class CalendarTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $calendar;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\Calendar';
    parent::setUp();
    $this->calendar = new Calendar();
  }
  
  public function testAcceptance() {
    $this->html = $this->calendar->html();
    
    $this->test->css('div.psc-cms-ui-calendar')->count(1);
  }
}
?>