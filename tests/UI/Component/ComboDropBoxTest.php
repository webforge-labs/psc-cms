<?php

namespace Psc\UI\Component;

/**
 * @group class:Psc\UI\Component\ComboDropBox
 */
class ComboDropBoxTest extends TestCase {
  
  public function setUp() {
    $this->componentClass = $this->chainClass = 'Psc\UI\Component\ComboDropBox';
    $this->testValue = array_slice($this->loadTestEntities('tags'),1,2);
    $this->expectedRule = 'Psc\Form\DropBox2ValidatorRule';
    parent::setUp();
  }
  
  public function testHTML() {
    // html wird dasselbe sein wie bei Psc\UI\ComboDropBox2 ;)
    // höchstens testen ob es gewrapped ist
    $this->setFixtureValues();
    
    $this->html = $this->component->getHTML();
    
    $this->test->css('.psc-cms-ui-combo-box')->count(1);
    $this->test->css('.psc-cms-ui-drop-box')
          ->count(1)
          ->test('.psc-cms-ui-button')
          ->count(2);
  }
  public function createComponent() {
    $comboDropBox = new ComboDropBox();
    $comboDropBox->dpi($this->getEntityMeta('Psc\Doctrine\TestEntities\Tag'), new \Psc\Doctrine\DCPackage());
    return $comboDropBox;
  }
}
?>