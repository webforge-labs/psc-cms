<?php

namespace Psc\Doctrine;

/**
 * @group class:Psc\Doctrine\UniqueConstraint
 */
class UniqueConstraintTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\UniqueConstraint';
    parent::setUp();
  }
  
  public function testHelperGeneration() {
    $uq = new UniqueConstraint('test', array('season_id'=>$this->getType('PositiveInteger'),
                                             'episode_num'=>$this->getType('PositiveInteger'),
                                             'release'=>$this->getType('String')
                                             ));
    $data = $uq->getDataHelper();
    $this->assertEquals(array('season_id'=>7, 'episode_num'=>2, 'release'=>'GDR'), $data(7,2,'GDR'));
    $this->assertEquals(array('season_id'=>7, 'episode_num'=>2, 'release'=>'GDR',
                              'identifier'=>4382
                        ),
                        $data(7,2,'GDR',4382)
                       );
    
    $this->assertException('InvalidArgumentException', function () use ($data) {
      $data(1);
    });
    $this->assertException('InvalidArgumentException', function () use ($data) {
      $data(1,2,'GDR',4382,'toomuch');
    });
  }
  
  public function createUniqueConstraint() {
    return new UniqueConstraint();
  }
}
?>