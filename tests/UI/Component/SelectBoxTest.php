<?php

namespace Psc\UI\Component;

/**
 * @group class:Psc\UI\Component\SelectBox
 */
class SelectBoxTest extends TestCase {
  
  protected $selectBox;
  
  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\SelectBox';
    $this->testValue = 'v2';
    $this->values = Array( // so würden die values aus dem EnumType gesetzt werden
      'v1'=>'v1',
      'v2'=>'v2'
    );
    $this->expectedRule = 'Psc\Form\ValuesValidatorRule';
    parent::setUp();
  }
  
  public function testRespectsTypeValidationRule() {
    $c = $this->createComponent();
    
    $typeMock = $this->getMock('Psc\Data\Type\EnumType', array('getValidatorRule'), array(\Psc\Data\Type\Type::create('String'), array('v1','v2')));
    
    $typeMock->expects($this->once())->method('getValidatorRule')->with($this->isInstanceOf('Psc\Data\Type\TypeRuleMapper'));
    
    $c->setType($typeMock);
    $c->init();
  }
  
  public function testHTML() {
    $this->setFixtureValues();
    
    $this->html = $this->component->getHTML();
    
    $input = $this->test->css('select', $this->html)
      ->count(1)
      ->hasAttribute('name', 'testName')
      ->test('option[value="v1"]')->count(1)->hasText('label1')->end()
      ->test('option[value="v2"]')->count(1)->hasText('label2')->end()
      ->getJQuery();
    
    $this->assertLabelFor($input->attr('id'), $this->html);
  }
  
  public function createComponent() {
    $box = new SelectBox();
    $labeler = new \Psc\CMS\Labeler();
    $labeler->label('v1','label1');
    $labeler->label('v2','label2');
    
    $box->dpi($this->values, $labeler);
    return $box;
  }
}
?>