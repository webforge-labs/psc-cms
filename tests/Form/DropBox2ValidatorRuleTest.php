<?php

namespace Psc\Form;

/**
 * @group class:Psc\Form\DropBox2ValidatorRule
 */
class DropBox2ValidatorRuleTest extends \Psc\Doctrine\DatabaseTest {
  
  protected $dropBox2ValidatorRule;
  
  public function setUp() {
    $this->con = 'tests';
    $this->chainClass = 'Psc\Form\DropBox2ValidatorRule';
    parent::setUp();
    $this->rule = new DropBox2ValidatorRule($this->getEntityMeta('Psc\Doctrine\TestEntities\Tag'), $this->getDoctrinePackage());
  }

  public function testInsertFixture() {
    $this->dcFixtures->add(new \Psc\Doctrine\TestEntities\TagsFixture());
    $this->dcFixtures->execute();
  }

  public function provideValidData() {
    $tests = array();
    $data = function ($formInput) use (&$tests) {
      $tests[] = array($formInput);
    };
    
    $data(array(2,3,4));
    $data(array(2));
    
    return $tests;
  }
  
  /**
   * @dataProvider provideValidData
   */
  public function testValid(Array $data) {
    $expectedTags = new \Psc\Data\ArrayCollection();
    foreach ($data as $tagId) {
      $expectedTags[] = $this->hydrate('Psc\Doctrine\TestEntities\Tag', $tagId);
    }
    
    $this->assertEntityCollectionSame($expectedTags, $this->rule->validate($data));
  }
  
  public function provideEmptyData() {
    $tests = array();
    $data = function ($formInput) use (&$tests) {
      $tests[] = array($formInput);
    };
    
    $data(array());
    $data(NULL);
    
    return $tests;
  }

  /**
   * @dataProvider provideEmptyData
   * @expectedException Psc\Form\EmptyDataException
   */
  public function testEmpty($data) {
    $this->rule->validate($data);
  }


  /**
   * @dataProvider provideInvalidData
   */
  public function testInvalidData($data=NULL, $exception = NULL) {
    $this->setExpectedException($exception ?: '\Psc\Form\ValidatorRuleException');
    
    $this->rule->validate($data);
  }

  public static function provideInvalidData() {
    $tests = array();
    
    $invalid = function ($formInput, $exception = NULL) use (&$tests) {
      $tests[] = array($formInput, $exception);
    };
    
    $invalid(array('invalidId','invalidSecondId',7));
    $invalid(array(0.32));
    $invalid(array(0));
    $invalid(array(-1));
    $invalid(array(-100));
    $invalid('notanarray', 'InvalidArgumentException');
    $invalid(array(2, 0, 3));
    
    // complex: one tag is missing:
    $invalid(array(2, 122, 3), 'Psc\Doctrine\EntityNotFoundException'); // mittleres ist bestimmt nicht als id in der datenbank
    
    
    return $tests;
  }
}
?>