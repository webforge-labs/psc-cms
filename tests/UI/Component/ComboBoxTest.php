<?php

namespace Psc\UI\Component;

/**
 * @group class:Psc\UI\Component\ComboBox
 */
class ComboBoxTest extends TestCase {
  
  protected $comboBox;
  
  public function setUp() {
    $this->componentClass = $this->chainClass = 'Psc\UI\Component\ComboBox';
    $this->expectedRule = 'Psc\Form\SelectComboBoxValidatorRule';
    $this->testValue = new \Psc\Doctrine\TestEntities\Person('Me', 'p.scheit@ps-webforge.com');
    parent::setUp();
  }
  
  public function testAcceptance() {
    // der test kann total acceptance sein, weil wir alle details in der combobox selber machen. Hier schauen wir nur ob die Combobox mithilfe der Dependencies konstruiert werden kann
    
    $component = $this->createComponent();
    $this->setFixtureValues($component);
    
    $this->html = $component->getHTML();
    $this->test->css('input.psc-cms-ui-combo-box',$this->html)
      ->count(1)
      ->attribute('name',$this->logicalNot($this->equalTo('testName')));
  }
  
  public function createComponent() {
    $c = parent::createComponent()->dpi($this->getEntityMeta('Psc\Doctrine\TestEntities\Person'),
                                          new \Psc\Doctrine\DCPackage()
                                         );
    
    return $c;
  }
}
?>