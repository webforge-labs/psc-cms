<?php

namespace Psc\CMS\Item;

/**
 * @group class:Psc\CMS\Item\RightContentLink
 */
class RightContentLinkTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $link;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Item\RightContentLink';
    parent::setUp();
    
    $this->link = new RightContentLink(
      new \Psc\CMS\RequestMeta('GET', '/api/products/test/some/custom/action'), 'My Custom Action'
    );
  }
  
  public function testInterfaces() {
    $this->assertInstanceOf('Psc\Data\Type\Interfaces\Link', $this->link);
    $this->assertInstanceOf('Psc\CMS\Item\RightContentLinkable', $this->link);
  }
  
  public function testHTMLAcceptanceInDropList() {
    $list = new \Psc\UI\DropContentsList();
    $list->addLinkable($this->link);
    $this->html = $list->html();
    
    $this->test->css('a.psc-cms-ui-drop-content-ajax-call')->count(1);
    $this->test->css('a[class*="psc-guid"]')->count(1);
    $this->test->css('a')->count(1)->hasAttribute('href', '/api/products/test/some/custom/action')->hasText('My Custom Action');
  }
}
?>