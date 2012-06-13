<?php

namespace Psc\CMS\Item;

use Psc\Code\Generate\GClass;

/**
 * @group class:Psc\CMS\Item\JooseBridge
 */
class JooseBridgeTest extends \Psc\Code\Test\Base {
  
  protected $jooseBridge;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Item\JooseBridge';
    parent::setUp();
    $this->item = $this->getEntityMeta('Psc\Doctrine\TestEntities\Article')->getAdapter(current($this->loadTestEntities('articles')));
    $this->jooseBridge = new JooseBridge($this->item);
  }
  
  public function testAcceptance() {
    $tag = new \Psc\HTML\Tag('button','somelabel');
    
    $this->html = $this->jooseBridge->link($tag)->html();
    
    // schwierig zu testen, weil eigentlich wollen wir wissen ob das $tag das beim html() angehängt hat (acceptance)
    // somit machen wir hier nur den joosebridge unit test
    $traits = $this->test->js($this->jooseBridge)
      ->constructsJoose('Psc.CMS.Item')
      ->hasParam('traits')
      ->hasParam('widget')
      ->getParam('traits');
      
    $traits = array_map(function ($code) {
      return $code->js();
    },$traits); // cast to string
      
    $this->assertContains('Psc.CMS.ComboDropBoxable', $traits, print_r($traits, true));
    // diese beiden nicht weil diese schon in ComboDropBoxable drin sind
    $this->assertNotContains('Psc.CMS.DropBoxable', $traits, print_r($traits, true));
    $this->assertNotContains('Psc.CMS.SelectComboBoxable', $traits, print_r($traits, true));
  }
  
  public function testConstructInterfaceHierarchy() {
    return $this->assertTrue(true, 'einkommentieren zum compilieren');
    
    $avaibleInterfaces = array(
      'ComboDropBoxable',
      'DropBoxButtonable',
      'SelectComboBoxable',
      'GridRowable',
      'RightContentLinkable',
      'Searchable',
      'AutoCompletable',
      'Patchable',
      'NewButtonable',
      'NewTabButtonable',
      'DeleteButtonable',
      'EditButtonable',
      'TabButtonable',
      'TabLinkable',
      'TabOpenable',
      'Buttonable',
      'Deleteable',
      'Identifyable'
    );
    
    $hierarchy = array();
    foreach ($avaibleInterfaces as $avaibleInterface) {
      $hierarchy[$avaibleInterface] = array();
      foreach (GClass::factory('Psc\CMS\Item\\'.$avaibleInterface)->getAllInterfaces() as $interface) {
        if ($interface->getNamespace() === '\Psc\CMS\Item') {
          $hierarchy[$avaibleInterface][] = $interface->getFQN();
        }
      }
    }
    
    $codeWriter = new \Psc\Code\Generate\CodeWriter();
    
    $code  = '    $avaibleInterfaces = Array('."\n";
    foreach ($hierarchy as $trait => $interfaces) {
      $code .= sprintf("      '%s' => %s,\n", $trait, $codeWriter->exportList($interfaces));
    }
    $code .= '    );'."\n";
    
    print "\n\n";
    print $code;
    print "\n";
  }
}
?>