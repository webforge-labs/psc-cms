<?php

namespace Psc\JS;

/**
 * @group class:Psc\JS\jQueryTemplate
 */
class jQueryTemplateTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $jQueryTemplate;
  
  public function setUp() {
    $this->chainClass = 'Psc\JS\jQueryTemplate';
    parent::setUp();
    $this->jQueryTemplate = new jQueryTemplate(
      '<button type="button" class="page" rel="${page.num}">${page.num}</button>'
    );
  }
  
  public function testAcceptance() {
    $this->html = $this->jQueryTemplate->html();
    
    $this->test
      ->css('script[type="x-jquery-tmpl"]')->count(1);
      
    $this->assertContains('<button type="button" class="page" rel="${page.num}">${page.num}</button>', $this->html);
  }
}
?>