<?php

namespace Psc\DateTime;


/**
 * @group class:Psc\DateTime\Date
 */
class DateTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\DateTime\Date';
    parent::setUp();
  }

  public function testConstruct() {
    
    // createm from datetime
    $dateTime = new DateTime('21.11.84 21:12');
    $date = Date::createFromDateTime($dateTime);
    $this->assertInstanceOf('Psc\DateTime\Date',$date);
    $this->assertEquals('21.11.1984 00:00:00',$date->format('d.m.Y H:i:s'));

  }
  
  /**
   * @expectedException Webforge\Common\DateTime\ParsingException
   */
  public function testInvalidDate() {
    $date = new Date('29.02.2011'); // 2012 ist das schaltjahr
    
    print $date->format('d.m.Y');
  }

  public function createDate() {
    return new Date();
  }
}
?>