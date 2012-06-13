<?php

namespace Psc\CMS\Item;

/**
 * @group class:Psc\CMS\Item\Adapter
 */
class AdapterTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $adapter;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Item\Adapter';
    parent::setUp();
    
    $this->entity = current($this->loadTestEntities('articles'));
    $this->entity->setId(7);
    $this->adapter = new Adapter($this->entity, $this->getEntityMeta('Psc\Doctrine\TestEntities\Article'));
  }
  
  public function testGetTabButtonAcceptance() {
    $button = $this->adapter->getTabButton(); // gibt einen Button zurück der den DefaultTab (Formular) des Entities aufmachen kann
    $this->assertInstanceOf('Psc\UI\ButtonInterface', $button);
    
    $button->setLabel('My Custom Label');
    
    $this->html = $button->html();
    
    $this->test->css('button.psc-cms-ui-button')
               ->count(1)
               ->hasText('My Custom Label');
    
    $this->test->js($button)
               ->constructsJoose('Psc.CMS.Item')
               ->hasParam('tab', $this->isType('object'))
               ->hasParam('button', $this->isType('object'))
               ;
               
    // constructs joose ist hier etwas komplexer (wegen Role und Psc.CMS.Item und so). Wir überlassen dies aber dem TabButtonTest
  }

  public function testGetRightContentLinkable() {
    $this->assertInstanceOf('Psc\CMS\Item\RightContentLinkable', $rcLinkable = $this->adapter->getRCLinkable());
    
    $this->assertEquals('article', $rcLinkable->getEntityName());
    $this->assertEquals(7, $rcLinkable->getIdentifier());
  }
  
  public function testGetTabOpenableReturnsDefaultRequestMetaNotTabRequestMetaOnDefault() {
    $this->MarkTestIncomplete('todo');
  }

  public function testGetSelectComboBoxable() {
    $this->assertInstanceOf('Psc\CMS\Item\SelectComboBoxable', $this->adapter->getSelectComboBoxable());
  }

  public function testGetDeleteButtonable() {
    $this->assertInstanceOf('Psc\CMS\Item\DeleteButtonable', $this->adapter->getDeleteButtonable());
    $this->assertInstanceOf('Psc\UI\Button', $this->adapter->getDeleteTabButton());
  }

  public function testGetComboDropBoxable() {
    $this->assertInstanceOf('Psc\CMS\Item\ComboDropBoxable', $this->adapter->getComboDropBoxable());
  }
}
?>