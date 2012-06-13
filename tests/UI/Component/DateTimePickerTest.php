<?php

namespace Psc\UI\Component;

use Psc\UI\Component\DateTimePicker;
use Psc\DateTime\DateTime;

/**
 * @group class:Psc\UI\Component\DateTimePicker
 * @group component
 */
class DateTimePickerTest extends TestCase {

  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\DateTimePicker';
    parent::setUp();
    $this->testValue = $this->dateTime = new DateTime('21.11.1984 02:00:01');
  }

  public function testHTML() {
    $this->component->setFormName('testName');
    $this->component->setFormValue($this->dateTime);
    $this->component->setFormLabel('testLabel');
    
    $html = $this->component->getHTML();
    
    $input = $this->test->css('input[type="text"][name="testName[date]"]', $html)
      ->count(1, 'input testName[date] nicht gefunden')
      ->hasAttribute('name', 'testName[date]')
      ->hasAttribute('value', '21.11.1984')
      ->getJQuery();
    
    $this->assertLabelFor($input->attr('id'), $html);

    $input = $this->test->css('input[type="text"][name="testName[time]"]', $html)
      ->count(1, 'input testName[time] nicht gefunden')
      ->hasAttribute('name', 'testName[time]')
      ->hasAttribute('value', '02:00')
      ->getJQuery();
  }

  public function createDateTimePicker() {
    return new DateTimePicker();
  }
}
?>