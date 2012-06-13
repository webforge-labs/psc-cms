<?php

namespace Psc\Code\Test;

use Psc\Code\Test\FrontendCodeTester;

/**
 * @group class:Psc\Code\Test\FrontendCodeTester
 */
class FrontendCodeTesterTest extends \Psc\Code\Test\Base {
  
  protected $fct;

  public function setUp() {
    $this->chainClass = 'Psc\Code\Test\FrontendCodeTester';
    parent::setUp();
    $this->fct = new FrontendCodeTester($this);
  }

  /**
   * @dataProvider provideConstructorParams
   */
  public function testCSSConstruct($selector, $html = NULL) {
    if (func_num_args() === 2)
      $this->assertInstanceOf('Psc\Code\Test\CSSTester', $this->fct->css($selector, $html));
    else
      $this->assertInstanceOf('Psc\Code\Test\CSSTester', $this->fct->css($selector));
  }
  
  public static function provideConstructorParams() {
    return Array(
      array('input', '<form><input></form>'),
      array(new \Psc\JS\jQuery('input', '<form><input></form>'), NULL)
    );
  }

  public function createFrontendCodeTester() {
    return new FrontendCodeTester();
  }
}
?>