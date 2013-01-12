<?php

namespace Psc\Doctrine;

use Psc\Data\Type\Type;

/**
 * @group class:Psc\Doctrine\UniqueConstraintValidator
 */
class UniqueConstraintValidatorTest extends \Psc\Code\Test\Base {
  
  protected $validator;
  protected $constraint;
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\UniqueConstraintValidator';
    parent::setUp();
    $this->validator = $this->createUniqueConstraintValidator(
      $this->constraint = new UniqueConstraint('episode',
                           array('season_id'=> Type::create('PositiveInteger'),
                                 'num'=> Type::create('PositiveInteger'),
                                 'release'=> Type::create('String')
                                 )
                           )
    );
  }
  
  public function testConstruct() {
    $this->assertChainable($this->validator);
  }
  
  public function testProcessAcceptance() {
    $data = $this->getDataHelper();
    
    $this->assertTrue($this->validate($data(27, 1, 'frm')));
    $this->assertTrue($this->validate($data(27, 2, 'frm')));
    $this->assertTrue($this->validate($data(27, 3, 'frm')));
    $this->assertTrue($this->validate($data(27, 3, 'dimension')));
    $this->assertTrue($this->validate($data(27, 3, 'gdr')));
    $this->assertTrue($this->validate($data(27, 4, 'dimension')));
    $this->assertTrue($this->validate($data(28, 4, 'dimension')));    
    
    $this->assertFalse($this->validate($data(27, 1, 'frm')));
    $this->assertFalse($this->validate($data(27, 3, 'dimension')));
    $this->assertFalse($this->validate($data(27, 4, 'dimension')));
    $this->assertFalse($this->validate($data(28, 4, 'dimension')));
  }
  
  public function testProcess_throwsExceptionWithInformation() {
    $data = $this->getDataHelper();
    
    $this->validator->process($data(27, 1, 'frm', 3247)); // das letzte ist der identifier
    $this->validator->process($data(27, 1, 'gdr', 3248)); // das letzte ist der identifier
    
    try {
      $this->validator->process($data(27, 1, 'frm'));
      
      $this->fail('keine UniqueConstraintException wurde geworfen');
    } catch (UniqueConstraintException $e) {
      $this->assertEquals($data(27,1,'frm'), $e->duplicateKey, 'Duplicate Key ist in der Exception falsch gesetzt');
      $this->assertEquals(3247, $e->duplicateIdentifier, 'Identifier falsch in der Exception gesetzt');
      $this->assertEquals($this->constraint->getName(), $e->uniqueConstraint, 'Name des Constraints ist falsch');
    }

    try {
      $this->validator->process($data(27, 1, 'gdr'));
      
      $this->fail('keine UniqueConstraintException wurde geworfen');
    } catch (UniqueConstraintException $e) {
      $this->assertEquals($data(27,1,'gdr'), $e->duplicateKey, 'Duplicate Key ist in der Exception falsch gesetzt');
      $this->assertEquals(3248, $e->duplicateIdentifier, 'Identifier falsch in der Exception gesetzt');
      $this->assertEquals($this->constraint->getName(), $e->uniqueConstraint, 'Name des Constraints ist falsch');
    }
  }
  
  protected function validate(Array $data) {
    try {
      $this->validator->process($data);
    } catch (UniqueConstraintException $e) {
      return FALSE;
    }
    
    return TRUE;
  }
  
  public function testUpdateIndex() {
    $data = $this->getDataHelper();
    
    // das sind dieselben daten wie im "true" block, oben
    $rows[] = $data(27, 1, 'frm');
    $rows[] = $data(27, 2, 'frm');
    $rows[] = $data(27, 3, 'frm');
    $rows[] = $data(27, 3, 'dimension');
    $rows[] = $data(27, 3, 'gdr');
    $rows[] = $data(27, 4, 'dimension');
    $rows[] = $data(28, 4, 'dimension');
    $this->assertChainable($this->validator->updateIndex($this->constraint, $rows));
    
    $this->assertFalse($this->validate($data(27, 1, 'frm')));
    $this->assertFalse($this->validate($data(27, 3, 'dimension')));
    $this->assertFalse($this->validate($data(27, 4, 'dimension')));
    $this->assertFalse($this->validate($data(28, 4, 'dimension')));
  }
  
  /**
   * @expectedException Psc\Data\Type\TypeExpectedException
   */
  public function testWrongData() {
    $this->validator->process(array('season_id'=>'string', 'num'=>1, 'release'=>'frm'));
  }
  
  /**
   * @expectedException Psc\Data\Type\WrongDataException
   */
  public function testMissingData() {
    $this->validator->process(array('season_id'=>7, 'release'=>'frm'));
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testUpdateIndex_WrongUniqueConstraint() {
    $this->validator->updateIndex(new UniqueConstraint('test', array('episode_id'=>Type::create('Integer'))),
                                  array()
                                  );
  }
  
  protected function getDataHelper() {
    return $this->constraint->getDataHelper();
  }
  
  public function createUniqueConstraintValidator($uqc) {
    return new UniqueConstraintValidator($uqc);
  }
}
?>