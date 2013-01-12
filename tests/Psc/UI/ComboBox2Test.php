<?php

namespace Psc\UI;

/**
 * @group class:Psc\UI\ComboBox2
 * @TODO avaible Items test (auch in JS)
 */
class ComboBox2Test extends \Psc\Code\Test\HTMLTestCase {
  
  protected $comboBox;
  
  protected $avaibleItems;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\ComboBox2';
    parent::setUp();
    $this->entityMeta = $this->getEntityMeta('Psc\Doctrine\TestEntities\Tag');
  }
  
  public function testAcceptanceWithAutoCompleteRequestMeta() {
    $this->avaibleItems = $this->entityMeta->getAutoCompleteRequestMeta();
    $this->comboBox = new ComboBox2('tags', $this->avaibleItems, $this->entityMeta);

    $this->html = $this->comboBox->html();
    
    $this->test->css('input.psc-cms-ui-combo-box',$this->html)
      ->count(1)
      ->attribute('name', $this->logicalNot($this->equalTo('tags')))
      ->hasAttribute('type','text')
    ;
    
    $this->test->js($this->comboBox)
               ->constructsJoose('Psc.UI.ComboBox')
               ->hasParam('name',$this->equalTo('tags')) // hier ist der richtige name
               ->hasParam('autoComplete')
              ;
  }
  
  public function testAcceptanceWithAvaibleItems() {
    $this->avaibleItems = $this->loadTestEntities('tags');
    $this->comboBox = new ComboBox2('tags', $this->avaibleItems, $this->getEntityMeta('Psc\Doctrine\TestEntities\Tag'));
    
    $this->html = $this->comboBox->html();
    
    $this->test->css('input.psc-cms-ui-combo-box',$this->html)
      ->count(1)
      ->attribute('name', $this->logicalNot($this->equalTo('tags'))) // weil das disabled sein muss. die value wird vom formcontroller serializiert
      ->hasAttribute('type','text')
    ;

    $autoComplete = $this->test->js($this->comboBox)
               ->constructsJoose('Psc.UI.ComboBox')
               ->hasParam('name',$this->equalTo('tags')) // hier ist der richtige name
               ->hasParam('autoComplete', $this->isType('object')) // sub
               ->getParam('autoComplete')
              ;

    $this->assertInstanceOf('Psc\JS\JooseSnippet',$autoComplete);
    $this->assertContains("'widget':",$autoComplete->js());
  }
  
  public function testSelectedItemIsSet() {
    $tag = current($this->loadTestEntities('tags'));
    $this->comboBox = new ComboBox2('tags', $this->entityMeta->getAutoCompleteRequestMeta(), $this->entityMeta);
    $this->comboBox->setSelected($tag);
    $this->html = $this->comboBox->html();
    
    $this->test->js($this->comboBox)
      ->constructsJoose('Psc.UI.ComboBox')
      ->hasParam('selected');
  }
  
  public function testSetUndGetMaxResults() {
    $this->avaibleItems = $this->entityMeta->getAutoCompleteRequestMeta();
    $this->comboBox = new ComboBox2('tags', $this->avaibleItems, $this->entityMeta);
    
    $this->assertNull(NULL, $this->comboBox->getMaxResults());
    $this->comboBox->setMaxResults(15);
    $this->assertEquals(15, $this->comboBox->getMaxResults());
  }
}
?>