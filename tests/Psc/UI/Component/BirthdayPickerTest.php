<?php

namespace Psc\UI\Component;

use Psc\UI\Component\BirthdayPicker;
use Psc\DateTime\DateTime;

/**
 * @group class:Psc\UI\Component\BirthdayPicker
 * @group component
 */
class BirthdayPickerTest extends TestCase {

  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\BirthdayPicker';
    parent::setUp();
    $this->testValue = new DateTime('21.11.1984 03:00');
  }
  
  public function testYearKnownBevaviour() {
    $picker = new BirthdayPicker();
    $this->assertTrue($picker->getYearKnown()); // default to true
    $this->setFixtureValues($picker); // does init
    
    $input = $this->test->css('input[name="testName"]', $picker->getInnerHTML())
                ->hasAttribute('value','21.11.1984');
    
    $this->assertTrue($picker->getYearKnown());
    
    $picker->setYearKnown(FALSE);
    $input = $this->test->css('input[name="testName"]', $picker->getInnerHTML())
                ->hasAttribute('value','21.11.');
  }
}
?>