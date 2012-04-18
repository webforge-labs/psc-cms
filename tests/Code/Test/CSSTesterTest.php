<?php

namespace Psc\Code\Test;

use Psc\Code\Test\CSSTester;

class CSSTesterTest extends \Psc\Code\Test\Base {

  protected $formHTML = <<< 'HTML_FORM'
<form class="main" action="" method="POST">
  <fieldset class="user-data group">
    <input type="text" name="email" value="" /><br />
    <br />
    <input type="text" name="name" value="" /><br />
  </fieldset>
  <fieldset class="password group">
    Bitte geben sie ein Passwort ein:<br />
    <input type="password" name="pw1" value="" /><br />
    Bitte best&auml;tigen sie das Passwort:<br />
    <input type="password" name="pw2" value="" /><br />
  </fieldset>
  <input type="hidden" name="submitted" value="true" />
  <input type="submit">
</form>
HTML_FORM;

  public function setUp() {
    $this->chainClass = 'Psc\Code\Test\CSSTester';
    parent::setUp();
  }

  // schade ich komme nicht auf die Idee wie man hier den TestCase so mockt, dass man mehr als acceptance testen kann
  public function testCountAcceptance() {
    $that = $this;
    $assertCountTest = function ($expectedSuccess, $testLabel, $selector, $html, $expected) use ($that) {
      $that->assertClosureTest(function ($innerTest) use ($expected, $selector, $html) {
        $tester = new CSSTester($innerTest, $selector, $html);
        $tester->count($expected);
      }, $expectedSuccess, $testLabel);
    };
    
    $counter = 0;
    $ok = function ($selector, $html, $expected) use ($that, $assertCountTest, $counter) {
      $assertCountTest(TRUE, 'CountTestFailure'.($counter++), $selector, $html, $expected);
    };
    $fail = function ($selector, $html, $expected, $num) use ($that, $assertCountTest, $counter) {
      $assertCountTest(FALSE, 'CountTestFailure'.($counter++), $selector, $html, $expected);
    };
    
    $ok('form.main',$this->formHTML, 1, 1);
    $ok('fieldset',$this->formHTML, 2, 2);
    $ok('input',$this->formHTML, 6, 3);
    $ok('form', '', 0, 4);
    
    $fail('form.blubb', $this->formHTML, 1, 1);
    $fail('form', $this->formHTML, 2, 2);
    $fail('form', '', 2, 2);
    //$ex('', '', 2, 2);
  }

  public function createTest(\Closure $innerTestCode) {
    $innerTestCase = $this->doublesManager->createClosureTestCase(function () use ($selector, $html) {
    });
  }
}
?>