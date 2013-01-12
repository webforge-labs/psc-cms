<?php

namespace Psc\CSS;

/**
 * @group class:Psc\CSS\Helper
 */
class HelperTest extends \Psc\Code\Test\Base {
  
  protected $helper;
  
  public function setUp() {
    $this->chainClass = 'Psc\CSS\Helper';
    parent::setUp();
  }
  
  public function testLoadAcceptance() {
    $this->assertEquals(
      '<link rel="stylesheet" media="all" type="text/css" href="/css/test/file.css" />',
      (string) Helper::load('/css/test/file.css')
    );
  }
}
?>