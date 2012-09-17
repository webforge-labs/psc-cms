<?php

namespace Psc\UI\Component;

use Psc\UI\Component\DatePicker;
use Psc\DateTime\DateTime;

/**
 * @group class:Psc\UI\Component\DatePicker
 * @group component
 */
class DatePickerTest extends TestCase {

  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\DatePicker';
    parent::setUp();
    $this->testValue = $this->dateTime = new DateTime('21.11.1984 02:00:01');
  }
  
  public function testHTML() {
    $this->assertStandardInputHTML('21.11.1984');
  }
  
  public function testHTML_DateFormat() {
    $this->markTestSkipped('not yet ready: setDateFormat blocked weil ich das parsen nicht hinbekomme (in der rule)');
    $this->component->setDateFormat('d.m.y');
    $this->assertStandardInputHTML('21.11.84');

    $this->component->setDateFormat('d.m:Y');
    $this->assertStandardInputHTML('21.11:1984');
  }
  
  public function testJavascriptJooseCall() {
    $this->markTestSkipped('geht nicht weil: hier waere es schöner den AST vom JooseSnippet laden zu können, denn so haben wir probleme mit dem %selector% z.B.');
    list($html, $input) = $this->assertStandardInputHTML('21.11.1984');
    
    $actualJs = $this->test->css('script')->getJQuery()->html();
    $expectedJs = <<<'JAVASCRIPT'
use('Psc.UI.DatePicker', function() {
  var j = new Psc.UI.DatePicker({
    'dateFormat': "d.m.y",
    'widget': %selector%.find('input.datepicker-date')
  })
});
JAVASCRIPT;
    
    $this->assertJavaScriptEquals($expectedJs, $actualJs);
  }
}
?>