<?php

namespace Psc\UI;

/**
 * @group class:Psc\UI\Tabs2
 */
class Tabs2Test extends \Psc\Code\Test\HTMLTestCase {
  
  protected $tabs;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\Tabs2';
    parent::setUp();

    $welcome = $this->doublesManager->createTemplateMock('willkommen im psc-cms', $this->once());
    $this->tabs = new Tabs2(array(), $welcome);
  }
  
  public function testAddsOpenable() {
    $this->tabs->addTabOpenable($this->getEntityMeta('Psc\Entities\User')->getAdapter('grid')->getTabOpenable());
    $this->tabs->init();
    
    $this->html = $this->tabs->html();
    
    // damit das css für das ui close icon direkt beim laden matched und nicht wenn js geladen ist
    // sonst werden tabs umgebrochen angezeigt in browsern wie chrome
    $this->test->css('div.psc-cms-ui-tabs ul.ui-tabs-nav')->count(1);
    
    $this->test->css('div.psc-cms-ui-tabs ul li')
      ->count(2, 'zwei tabslinks müssen vorhanden sein. (einer wurde hinzugefügt)');
  }
  
  public function testAcceptance() {
    $tabs = $this->tabs;
    $tabs->init();
    
    $tabs->select($selected = 1); // eigentlich unnütz
    
    $this->assertInstanceof('Psc\HTML\HTMLInterface', $tabs);
    
    $this->html = $html = $tabs->html();
    $this->test->css('div.psc-cms-ui-tabs', $html)
      ->count(1, 'ein div.psc-cms-ui-tabs muss vorhanden sein');
      
    $this->test->css('div.psc-cms-ui-tabs ul', $html)
      ->count(1, 'in div.psc-cms-ui-tabs muss ein ul vorhanden sein.');

    $tab0A = $this->test->css('div.psc-cms-ui-tabs ul li a', $html)
      ->count(1, 'in div.psc-cms-ui-tabs muss ein ul mit einem li mit dem Titel willkommen vorhanden sein.')
      ->hasText('Willkommen')
      ->getJQuery()
    ;
    
    $this->test->css('div.psc-cms-ui-tabs ul li span.ui-icon-close', $html)
      ->count(0, 'Willkommen darf keinen close span haben');
    
    $href = $tab0A->attr('href');
    $this->assertRegExp('/^#([-a-z0-9_]+)$/', $href, 'Href: '.$href.' ist im falschen format: (nur a-z0-9 - und _)');
    
    // existiert div#<href>  ?
    $content0 = $this->test->css($sel = sprintf('div.psc-cms-ui-tabs div[id="%s"]', mb_substr($href,1)), $html)
      ->count(1, $sel.' muss existieren. der Link in ul verlinkt falsch!')
      ->getJQuery()
    ;
    
    $this->assertNotEmpty($content0->html(), 'Content vom ersten Tab ist leer!');
    
    $this->assertRegExp(sprintf("/tabs.select\(/"), (string) $html, "tabs.select( konnte nicht gefunden werden");
  }
}
?>