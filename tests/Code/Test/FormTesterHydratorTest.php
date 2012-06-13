<?php

namespace Psc\Code\Test;

use Psc\Code\Test\FormTesterHydrator;
use \Psc\Doctrine\EntityDataRow;

/**
 * @group class:Psc\Code\Test\FormTesterHydrator
 */
class FormTesterHydratorTest extends \Psc\Code\Test\Base {

  public function testCollectionHydration() {
    $doctrineHydratorMock = $this->getMock('Psc\Doctrine\Hydrator', array(), array('MyTestEntity'));
    $doctrineHydratorMock->expects($this->once())
         ->method('byList')
         ->will($this->returnValue(array()));
    
    $hydrator = $this->getHydrator('MyTestEntity');
    $hydrator->setDoctrineHydratorFor('tags',$doctrineHydratorMock);
    
    $hydrator->getData()->setExpectedValue('tags',array());
    
    $collection = $hydrator->collection('tags', 'label');
    
    $this->assertEquals(array(),$hydrator->getData()->getExpectedValue('tags'));
  }
  
  public function testDoctrineHydratorSetAndGet() {
    $hydrator = $this->getHydrator('MyTestEntity');
    
    
    $this->assertException('Psc\Code\Test\MissingPropertyHydratorException', function () use ($hydrator) {
      $hydrator->getDoctrineHydratorFor('tags');
    });
    
    $dcHydrator = new \Psc\Doctrine\Hydrator('Entities\SoundTag');
    $hydrator->setDoctrineHydratorFor('tags', $dcHydrator);
    
    $this->assertInstanceOf('Psc\Doctrine\Hydrator',$hydrator->getDoctrineHydratorFor('tags'));
  }
  
  protected function getHydrator($entity = 'MyTestEntity') {
    return new FormTesterHydrator(
                                  new FormTesterData(new EntityDataRow($entity),
                                                     new EntityDataRow($entity)
                                                     ),
                                  \Psc\Doctrine\Helper::em() // wird eh nicht benutzt, da wird den doctrineHydrator ja mocken
                                  );
  }
}
?>