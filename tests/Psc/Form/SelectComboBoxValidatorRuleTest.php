<?php

namespace Psc\Form;

/**
 * @group class:Psc\Form\SelectComboBoxValidatorRule
 */
class SelectComboBoxValidatorRuleTest extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $rule;
  
  public function setUp() {
    $this->chainClass = 'Psc\Form\SelectComboBoxValidatorRule';
    $this->con = 'tests';
    parent::setUp();
    
    $this->rule = new SelectComboBoxValidatorRule('Psc\Doctrine\TestEntities\Tag', $this->getDoctrinePackage());
    $this->dcFixtures->add(new \Psc\Doctrine\TestEntities\TagsFixture);
  }
  
  public function testHydratesAvaibleTag() {
    $this->dcFixtures->execute();
    
    $ec = 'Psc\Doctrine\TestEntities\Tag';
    
    $this->assertInstanceOf($ec, $tag = $this->rule->validate(2));
    $this->assertEquals('Demonstration',$tag->getLabel());
  }
  
  /**
   * @expectedException Psc\Form\EmptyDataException
   */
  public function testThrowsEmptyExceptionOnNotAvaibleEntity() {
    $this->rule->validate(11);
  }
  
  /**
   * @expectedException Psc\Form\EmptyDataException
   * @dataProvider getEmptyData
   */
  public function testThrowsEmptyExceptionOnEmptyData($data) {
    $this->rule->validate($data);
  }
  
  public function getEmptyData() {
    return array(
      array(''),
      array(NULL)
    );
  }
}
?>