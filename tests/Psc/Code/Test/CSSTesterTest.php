<?php

namespace Psc\Code\Test;

use Psc\Code\Test\CSSTester;

/**
 * @group class:Psc\Code\Test\CSSTester
 */
class CSSTesterTest extends \Psc\Code\Test\Base {

  protected static $formHTML = <<< 'HTML_FORM'
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

  /**
   * @dataProvider provideCountAcceptance
   */
  public function testCountAcceptance($selector, $html, $expected, $success = TRUE) {
    if (!$success)
      $this->expectAssertionFail();
    
    $tester = new CSSTester($this, $selector, $html);
    $tester->count($expected);
  }
  
  public static function provideCountAcceptance() {
    $tests = array();
    
    $ok = function ($selector, $html, $expected) use (&$tests) {
      $tests[] = array($selector, $html, $expected, TRUE);
    };
    
    $fail = function ($selector, $html, $expected) use ($tests) {
      $tests[] = array($selector, $html, $expected, FALSE);
    };
    
    $ok('form.main',self::$formHTML, 1);
    $ok('fieldset',self::$formHTML, 2);
    $ok('input',self::$formHTML, 6);
    $ok('form', 'empty', 0, 4); // '' als html ist nicht (mehr) erlaubt
    
    $fail('form.blubb', self::$formHTML, 1);
    $fail('form', self::$formHTML, 2);
    $fail('form', '', 2);
    
    return $tests;
  }
  
  public function testWholeDocumentIsParsedAsDomDocumentToJquery() {
    $this->markTestIncomplete('TODO');
  }
}
?>