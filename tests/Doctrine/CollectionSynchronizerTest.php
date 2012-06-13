<?php

namespace Psc\Doctrine;

use Psc\Doctrine\CollectionSynchronizer;
use Psc\Doctrine\TestEntities\Tag;
use Psc\Data\ArrayCollection;

/**
 * @group class:Psc\Doctrine\CollectionSynchronizer
 */
class CollectionSynchronizerTest extends \Psc\Doctrine\DatabaseTest {
  
  protected $normalTags;
  protected $savedTags;
  
  public function configure() {
    $this->con = 'tests';
    parent::configure();
  }
  
  public function setUp() {
    parent::setUp();
    
    $this->loadEntity('Tag');
  }
  
  public function getEntityName($shortName) {
    return 'Psc\Doctrine\TestEntities\\'.$shortName;
  }

  public function testUniqueSynchronization() {
    $collection = $this->createCollection();
    $functional = TRUE;
    $blockSize = 4;
    
    if (!$functional) {
      $ex = $this->getMockBuilder('Psc\Doctrine\UniqueConstraintException')
        ->disableOriginalConstructor()
        ->getMock();
    
      $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
          ->disableOriginalConstructor()
          ->getMock();
      
      $persisted = array();
      $saved = array();
      $savedTags = $this->savedTags;
      $em
        ->expects($this->any())
        ->method('flush')
        ->will($this->returnCallback(function () use ($savedTags, $ex, &$persisted, &$saved, $blockSize) {
        
        
          // breche ab (test) wenn mehr entities geflushed werden sollen als in blocksize angegeben
          if (count($persisted) > $blockSize)
            throw new \Psc\Exception('Blocksize überschritten: '.count($persisted));
          
          /*
            schmeisse eine Exception wenn in den gespeicherten Tags ein Tag ist welches schon in der
            Datenbank istUniqueConstraintException
          */
          $throw = FALSE;
          foreach ($persisted as $label)  {
            if (in_array($label, $savedTags)) {
              $throw = TRUE;
            }
          }
        
          if ($throw) {
          
            // flush war nicht erfolgreich, wir setzen persisted zurück (rollback)
            $persisted = array(); // nach flush immer leeren?
            throw $ex;
        
          } else {
          
            // flush war erfolgreich alle die wir geflushed haben fügen wir zu flushed hinzu
            $saved = array_merge($persisted, $saved);
            $persisted = array(); // nach flush immer leeren?
            return TRUE;
          }
        }))
        ;
      
      $em
        ->expects($this->any())
        ->method('persist')
        ->will($this->returnCallback(function ($entity) use (&$persisted) {
          $persisted[] = $entity->getLabel();
        }))
        ;
    } else {
      if (!$this->tableExists('test_tags'))
        $this->updateEntitySchema('Tag');
    
      // load tags fixture  
      $this->loadFixtures(array('test_tags'));

      $em = $this->em;
    }
    $test = $this;
    
    $meta = $this->getEntityMetadata('Tag');
    $synchronizer = new CollectionSynchronizer($meta, $em);
    $synchronizer->setStrategy(CollectionSynchronizer::STRATEGY_INSERT);
    $synchronizer->setIgnoreUniqueConstraints(TRUE);
    $synchronizer->setBlockSize($blockSize);
    $synchronizer->setCollection($collection);
    $synchronizer->setNewEntityManagerCallback(function ($oldEM) use ($test) {
      return $test->resetEntityManager();
    });
    $synchronizer->process();
    
    // in $saved stehen jetzt alle label die erfolgreich geflushed werden konnten
    // (und nicht durch eine uniqueException unterbrochen wurden
    // in $saved dürfen jetzt nur die elemente sein die vorher nicht in der datenbank waren
    if (!$functional) {
      $this->assertEquals(8, count($saved));
      $this->assertEquals($this->normalTags, $saved, 'Es sind nicht alle Tags gespeichert worden', 0,10,TRUE);
    } else {
      $this->assertRowsNum('Tag',8+6);
      $this->assertArrayEquals($this->savedTags, array_map(\Psc\Code\Code::castGetter('label'),
                                                    $synchronizer->getSkippedUniqueConstraintEntities()
                                                    ));
    }
  }
  
  /**
   * @expectedException Psc\Doctrine\CollectionSynchronizerException
   */
  public function testStrategyInsert_EntityManagerCallbackMustBeSet() {
    $synchronizer = $this->createSynchronizer();
    $synchronizer->setCollection(new ArrayCollection());
    
    $synchronizer->process();
  }

  /**
   * @expectedException Psc\Doctrine\BadEntityException
   */
  public function testStrategyInsert_NullEntityInCollection() {
    $synchronizer = $this->createSynchronizer();
    $test = $this;
    $synchronizer->setNewEntityManagerCallback(function ($oldEM) use ($test) {
      return $test->resetEntityManager();
    });
    
    $collection = new \Psc\Data\ArrayCollection(
      $this->hydrateCollectionByField('Tag', array('migration','integration','php'), 'label')
    );
    $collection->add(NULL);
    
    $synchronizer->setCollection($collection);
    $synchronizer->process();
  }
  
  protected function createSynchronizer() {
    $meta = $this->getEntityMetadata('Tag');
    $synchronizer = new CollectionSynchronizer($meta, $this->em);
    $synchronizer->setStrategy(CollectionSynchronizer::STRATEGY_INSERT);
    $synchronizer->setIgnoreUniqueConstraints(TRUE);
    return $synchronizer;
  }
  
  protected function createCollection() {
    $collection = new ArrayCollection();
    
    foreach (
      // nicht vorhandene tags
      $this->normalTags = 
      array('scope',
            'clean-up',
            'robots',
            'rollback',
            'voting',
            'views',
            'shop',
            'mysql'
            )
      as $label) {
      $collection->add(new Tag($label));
    }
      
    // vorhandene tags (sind im fixture)
    foreach (
      $this->savedTags = 
      array('migration',
            'integration',
            'php',
            'audio',
            'favorite',
            'locked') as $label) {
      $tag = new Tag($label);
      $collection->add($tag);
    }
      
    return $collection;
  }
}
?>