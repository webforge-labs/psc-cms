<?php

namespace Psc\Doctrine;

/**
 * @group class:Psc\Doctrine\EntityRepository
 */
class EntityRepositoryUniqueConstraintTest extends \Psc\Doctrine\RepositoryTest {
  
  public function setUp() {
    $this->fixtures = array(new \Psc\Doctrine\TestEntities\TechnicalTagsFixture);
    $this->entityClass = 'Psc\Doctrine\TestEntities\Tag';
    parent::setUp();
  }
  
  public function testConfigureUniqueConstraintValidator() {
    // wir brauchen einen Repository-Mock (wir self-stunnen getUniqueIndex
    $this->repository = $this->createRepository(array('getUniqueIndex'));
    
    // Tag Label Constraint
    $constraint = $this->createTagUniqueConstraint();
    
    // Daten (gleiche wie Fixture, aber nicht relevant)
    $dataRows = $this->createUQDataRows($constraint);
    
    // wir wollen, dass das self-stunned repository so tut als wäre dies ein Datenbank-Request gewesen
    $this->repository->expects($this->exactly(1))->method('getUniqueIndex')
                     ->with($this->isInstanceOf('Psc\Doctrine\UniqueConstraint'))
                     ->will($this->returnValue($dataRows));
    
    // Test
    $validator = $this->repository->configureUniqueConstraintValidator();
    $this->assertInstanceOf('Psc\Doctrine\UniqueConstraintValidator', $validator);
    
    // Assert: Unique Constraint(s) korrekt gesetzt?
    $this->assertEquals(array('tag_label'=>$constraint),
                        $validator->getUniqueConstraints()
                       );
    
    // Assert: Data
    // jede Row muss eine Exception schmeissen und diese soll korrekt gesetzt sein
    // dadurch wissen wir, dass der Index korrekt gefüllt ist
    foreach ($dataRows as $data) {
      $this->assertDataInIndex($validator, $data);
    }
  }
  
  protected function assertDataInIndex($validator, Array $data) {
    // pre
    $this->assertArrayHasKey('label', $data, 'Pre-Condition: Data hat kein Label gesetzt');
    $this->assertArrayHasKey('identifier', $data, 'Pre-Condition: Data hat keinen Identifier gesetzt');
    
    try {
      $validator->process($data);
      
      $this->fail('Es war eine UniqueConstraintException erwartet, aber keine wurde gecatched: '.Code::varInfo($data));
    } catch (UniqueConstraintException $e) {
      $this->assertEquals($e->uniqueConstraint, 'tag_label', 'unique constraint name ist falsch gesetzt');
      $this->assertEquals($e->duplicateKey, array('label'=>$data['label']), 'duplikate key daten sind falsch gesetzt');
      $this->assertEquals($e->duplicateIdentifier, $data['identifier'], 'identifier ist falsch gesetzt');
      return $e;
    }
  }
  
  public function testConfigureUniqueConstraintValidator_withGivenValidator() {
    // wir wollen, dass das self-stunned repository so tut als wäre dies ein Datenbank-Request gewesen
    $this->repository = $this->createRepository(array('getUniqueIndex'));
    $this->repository->expects($this->exactly(1))->method('getUniqueIndex')
                     ->with($this->isInstanceOf('Psc\Doctrine\UniqueConstraint'))
                     ->will($this->returnValue(array())); // tabelle ist leer


    $validator = new UniqueConstraintValidator($this->createTagUniqueConstraint());
    $returned = $this->repository->configureUniqueConstraintValidator($validator);
    $this->assertSame($validator, $returned);
  }
  
  
  public function testGetUniqueIndex() {
    // wir checken auch mit reihenfolge (dunno)
    $this->assertEquals($this->createUQDataRows($constraint = $this->createTagUniqueConstraint()),
                        $r = $this->repository->getUniqueIndex($constraint, function ($qb) {
                                                                              return $qb->addOrderBy('e.id','ASC');
                                                                            }
                                                              ),
                        "Index Return:\n".
                        "\n".
                        Helper::debugCollection($r)
                       );
  }

  protected function createUQDataRows(UniqueConstraint $constraint) {
    $data = $constraint->getDataHelper();
    
    $rows = array();
    
    // das sind die labels aus dem fixture, für den testGetUniqueIndex test ist das wichtig(!) (für den anderen nicht)
    $rows[] = $data('migration', 1);
    $rows[] = $data('integration', 2);
    $rows[] = $data('php', 3);
    $rows[] = $data('audio', 4);
    $rows[] = $data('favorite', 5);
    $rows[] = $data('locked', 6);
    
    return $rows;
  }
}
?>