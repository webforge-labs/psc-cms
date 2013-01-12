<?php

namespace Psc\UI\Component;

use Psc\UI\Component\DateTimePicker;
use Psc\DateTime\DateTime;
use Psc\Form\ComponentsValidator;

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
  
  public function testNormalSelfValidation() {
    $datePicker = $this->createComponent();
    $datePicker->setType($this->getType('DateTime'));
    $datePicker->setFormName('start');
    
    $componentsValidator = $this->getMockBuilder('Psc\Form\ComponentsValidator', array('validate'))
                            ->disableOriginalConstructor()
                            ->getMock();
    
    $datePicker->onValidation($componentsValidator);
    
    $this->assertFalse($datePicker->getValidatorRule()->getTimeIsOptional(),'timeIsOptional is set in validator rule');
  }
  
  public function testSpecialValidationWithTimeIsOptionalArgument() {
    $datePicker = $this->createComponent();
    $datePicker->setType($this->getType('DateTime'));
    $datePicker->setFormName('start');
    $datePicker->setTimeIsOptionalComponentName('start-is-whole-day');

    $componentsValidator = $this->getMockBuilder('Psc\Form\ComponentsValidator', array('validateComponent','getComponentByFormName'))
                            ->disableOriginalConstructor()
                            ->getMock();
                            
    $wholeDayComponent = new Checkbox();
    $wholeDayComponent->setFormName('start-is-whole-day');
    $wholeDayComponent->setFormValue(TRUE); // fake
    
    $componentsValidator->expects($this->once())->method('getComponentByFormName')
                        ->with($this->identicalTo('start-is-whole-day'))->will($this->returnValue($wholeDayComponent));

    $componentsValidator->expects($this->once())->method('validateComponent')
                        ->with($this->identicalTo($wholeDayComponent))->will($this->returnValue($wholeDayComponent));
    
    $datePicker->onValidation($componentsValidator);
    
    $this->assertTrue($datePicker->getValidatorRule()->getTimeIsOptional(),'timeIsOptional is not set in validator rule');
  }
}
?>