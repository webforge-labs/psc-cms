<?php

namespace Psc\UI\Component;

use Psc\UI\Component;

class TestCase extends \Psc\Code\Test\HTMLTestCase {
  
  protected $componentClass;
  
  protected $component;
  
  protected $testValue = 'testValue';
  
  /**
   * value FALSE  damit asserted wird, dass die componente keine rule hat
   * value string|Object damit die Rule component->getValidatorRule() äquivalent ist
   */
  protected $expectedRule = NULL;

  public function setUp() {
    $this->chainClass = 'Psc\UI\Component';
    $this->assertNotEmpty($this->componentClass,'$this->componentClass in setUp setzen!');    
    $this->test->object = $this->component = $this->createComponent();
    
    parent::setUp();
  }
  
  public function testConstructWithoutParams() {
    $c = $this->componentClass;
    $this->assertInstanceOf($this->componentClass, new $c());
  }

  public function testSetters() {
    $this->test->setter('formName', $this->createType('String'), '::random', 0, 'name');
    $this->test->setter('formLabel', $this->createType('String'), '::random', 0, 'label');
    
    if ($this->testValue === 'testValue') {
      $this->test->setter('formValue', $this->createType('String'), '::random', 0, 'value');
      $this->test->setter('formValue', $this->createType('Integer'), '::random', 0, 'value');
      $this->test->setter('formValue', $this->createType('Array'), '::random', 0, 'value');
    } else {
      $this->test->setter('formValue', NULL, $this->testValue, 0, 'value');
    }
    $this->test->setter('hint', $this->createType('String'));
  }
  
  public function testFormNameSetter_Exception() {
    $this->test->setterException('formName', 'InvalidArgumentException', array(
      '',
      new \stdClass,
      array()
    ));
  }
  
  public function testGetComponentName() {
    $this->assertNotEmpty($this->component->getComponentName());
  }
  
  public function testComponentRulesEqualsExpectedRule() {
    $this->component->init();
    if (isset($this->expectedRule)) {
      $this->assertTrue($this->component->hasValidatorRule(),'hasValidatorRule() is expected to return true: '.$this->expectedRule);
      $rule =$this->component->getValidatorRule();
      if (is_object($rule)) {
        $this->assertInstanceOf($this->expectedRule, $rule);
      } else {
        $this->assertEquals($this->expectedRule, $rule);
      }
    } elseif ($this->expectedRule === FALSE) {
      $this->assertFalse($this->component->hasValidatorRule());
    }
  }

  /**
   * Wir überprüfen, ob die Componente "außen" von einem div.input-set-wrapper ummantelt ist
   *
   * zusätzlich für acceptance tests auch component-wrapper als klasse und role
   * außerdem hat sie die klasse component-for-$componentFormName
   */
  public function testHTML_Wrapper() {
    $component = $this->createComponent();
    $this->setFixtureValues($component);
    $this->html = $component->getHTML();
    
    $this->test->css('div.component-wrapper.input-set-wrapper', $this->html)
      ->count(1);
  }
  
  public function testGetWrappedComponent() {
    $component = $this->createComponent();
    $this->setFixtureValues($component);
    $this->html = $component->getHTML();
    
    $this->assertInstanceof('Psc\HTML\Tag', $component->getWrappedComponent($this->html));
  }
  
  public function testHTML_Hint() {
    $component = $this->createComponent();
    $this->setFixtureValues($component);
    $component->setHint($hint = 'Try to accomplish that the user understands what i mean');
    
    if ($component->hasHint()) { // component can block
      
      $this->test->css('div.input-set-wrapper small.hint', $this->html = $component->getHTML())
        ->exists()
        ->hasText($hint)
      ;
    }
  }
  
  public function assertStandardInputHTML($testValue = 'testValue', $type = 'text') {
    $this->setFixtureValues();
    
    $this->html = $this->component->getHTML();
    
    $input = $this->test->css('input[type="'.$type.'"]', $this->html)
      ->count(1, 'kein input[type="'.$type.'"] gefunden')
      ->hasAttribute('name', 'testName')
      ->hasAttribute('value', $testValue)
      ->getJQuery();
    
    $this->assertLabelFor($input->attr('id'), $this->html);
    
    return array($this->html, $input);
  }
  
  public function assertLabelFor($id, $html, $labelText = 'testLabel') {
    // label muss die id vom input haben
    $this->test->css(sprintf('label[for="%s"]', $id), $html)
      ->count(1,sprintf('label for "%s" nicht gefunden, ',$id))
      ->hasClass('psc-cms-ui-label')
      ->hasText($labelText)
    ;
  }
  
  public function setFixtureValues($component = NULL) {
    if (!isset($component)) {
      $component = $this->component;
    }
    
    $component->setFormName('testName');
    $component->setFormLabel('testLabel');
    $component->setFormValue($this->testValue);
    
    $component->init();
  }

  public function createComponent() {
    $c = $this->componentClass;
    return new $c();
  }
}
?>