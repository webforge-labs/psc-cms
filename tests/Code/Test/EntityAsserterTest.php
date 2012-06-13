<?php

namespace Psc\Code\Test;

use Psc\Code\Test\EntityAsserter;
use Psc\Data\ArrayCollection;
use Psc\Doctrine\EntityDataRow;

/**
 * @group class:Psc\Code\Test\SoundEn
 */
class SoundEntity extends \Psc\Doctrine\Object {
  
  protected $id;
  protected $number = NULL;
  protected $label = NULL;
  protected $tags = NULL;
  protected $content = NULL;
  
  public function __construct($id, $number, $label, \Doctrine\Common\Collections\Collection $tags, $content) {
    $this->id = $id;
    $this->number = $number;
    $this->label = $label;
    $this->tags = $tags;
    $this->content = $content;
  }
  
  public function getEditLabel() {
    return $this->label;
  }
  
  public function getEntityName() {
    return 'Psc\Code\Test\SoundEntity';
  }
  
  public function getIdentifier() {
    return $this->id;
  }
}

class SoundTag extends \Psc\Doctrine\Object {
  
  protected $id;
  protected $label;
  
  public function __construct($id, $label) {
    $this->id = $id;
    $this->label = $label;
  }

  public function getEntityName() {
    return 'Psc\Code\Test\SoundTag';
  }
  
  public function getIdentifier() {
    return $this->id;
  }
}

class EntityAsserterTest extends \Psc\Code\Test\Base {
  
  protected $ea;
  protected $sameRow;
  protected $soundEntity;
  protected $tags = array();
  
  public function setUp() {
    parent::setUp();
    
    foreach (array('common','extremlyLoud','special') as $id=>$label) {
      $this->tags[$label] = new SoundTag(($id+1),$label);
    }
    
    $this->ea = new EntityAsserter($this->soundEntity = new SoundEntity(
      237,
      'psc0822387',
      NULL,
      new ArrayCollection(array($this->tags['special'], $this->tags['extremlyLoud'])),
      '[Programmierer schreiend] So kann ich nicht arbeiten!'
    ), $this); // $this wird hier in der closure mit dem inneren Test ersetzt
    
    /* Eine EntityDataRow die genau mit dem SoundEntity übereinstimmt */
    $this->sameRow = new EntityDataRow('SoundEntity',
      array('identifier'=>237,
            'number'=>'psc0822387',
            'label'=>NULL,
            'tags'=>new ArrayCollection(array($this->tags['special'], $this->tags['extremlyLoud'])),
            'content'=>'[Programmierer schreiend] So kann ich nicht arbeiten!'
            )
    );
    $this->sameRow->setMeta('tags', EntityDataRow::TYPE_COLLECTION);
  }

  public function testAsserter() {
    $this->markTestSkipped('Closure Testcase klappt nicht auf travis');
    $asserter = $this->ea;
    $tags = $this->tags;
    
    // alle assertions klappen
    $allSuccess = function ($test) use ($asserter, $tags) {
      $asserter->setTest($test);
      
      $asserter->same(237, 'identifier');
      $asserter->same('psc0822387', 'number');
      $asserter->same(NULL, 'getEditLabel');
      $asserter->equalsCollection(array($tags['special'], $tags['extremlyLoud']), 'tags');
      $asserter->same('[Programmierer schreiend] So kann ich nicht arbeiten!', 'content');
    };
    
    $equalsSuccess = function ($test) use ($asserter, $tags) {
      $asserter->setTest($test);
      $asserter->equals('', 'getEditLabel'); // equals ist nicht typsicher
    };

    $equalsFail = function ($test) use ($asserter, $tags) {
      $asserter->setTest($test);
      $asserter->same('', 'getEditLabel'); // same ist typsicher
    };


    $collectionSuccess = function ($test) use ($asserter, $tags) {
      $asserter->setTest($test);
      $asserter->equalsCollection(array($tags['extremlyLoud'],$tags['special']), 'tags'); // reihenfolge wurscht
    };

    $collectionFail = function ($test) use ($asserter, $tags) {
      $asserter->setTest($test);
      $asserter->equalsOrderedCollection(array($tags['extremlyLoud'],$tags['special']), 'tags'); // reihenfolge nicht wurscht
    };

    $fail3 = function ($test) use ($asserter, $tags) {
      $asserter->setTest($test);
      $asserter->equals(NULL, 'content');
    };
  
    $this->assertTest(new ClosureTestCase($allSuccess, 'allSuccess'), TRUE);
    $this->assertTest(new ClosureTestCase($equalsSuccess, 'equalsSuccess'), TRUE);
    $this->assertTest(new ClosureTestCase($collectionSuccess, 'collectionSuccess'), TRUE);

    $this->assertTest(new ClosureTestCase($collectionFail, 'collectionFail'), FALSE);
    $this->assertTest(new ClosureTestCase($equalsFail, 'equalsFail'), FALSE);
    $this->assertTest(new ClosureTestCase($fail3, 'fail3'), FALSE);
  }

  /**
   * @dataProvider provideAsserterAPI()
   */
  public function testAsserterAPI($label, $expectedSuccess, $closure) {
    $this->markTestSkipped('Closure Testcase klappt nicht auf travis');
    $asserter = $this->ea;
    $tags = $this->tags;
    
    $row = $this->sameRow; // ist exakt gleich (same) zum entity im asserter
    
    $outerClosure = function ($test) use ($closure, $asserter, $tags, $row) {
      $asserter->setTest($test);
      
      // kann sich test noch mit use(&$test) ranholen
      $closure($asserter, $row, $tags);
    
    };
    
    $this->assertTest(new ClosureTestCase($outerClosure, $label), $expectedSuccess);
  }
  
  public function provideAsserterAPI() {
    $tests[0] = array('assert => EntityRow (alle von row, mit row)', TRUE,
      function ($asserter, $row) {
        $asserter->assert($row, $row->getProperties(), \Psc\Code\Test\EntityAsserter::TYPE_EQUALS);
        $asserter->assert($row, $row->getProperties(), \Psc\Code\Test\EntityAsserter::TYPE_SAME);
        $asserter->assert($row, $row->getProperties());
      }
    );
    
    $tests[1] = array('assertProperty (einzeln)', TRUE,
      function ($asserter, $row, $tags) {
        $asserter->assertProperty('label', '', \Psc\Code\Test\EntityAsserter::TYPE_EQUALS);
        $asserter->assertProperty('label', NULL, \Psc\Code\Test\EntityAsserter::TYPE_SAME);
        $asserter->assertProperty('label', NULL);

        $asserter->assertProperty('number', 'psc0822387', \Psc\Code\Test\EntityAsserter::TYPE_EQUALS);
        $asserter->assertProperty('number', 'psc0822387', \Psc\Code\Test\EntityAsserter::TYPE_SAME);
        $asserter->assertProperty('number', 'psc0822387');
        
        $c = '[Programmierer schreiend] So kann ich nicht arbeiten!';
        $asserter->assertProperty('content', $c, \Psc\Code\Test\EntityAsserter::TYPE_EQUALS);
        $asserter->assertProperty('content', $c, \Psc\Code\Test\EntityAsserter::TYPE_SAME);
        $asserter->assertProperty('content', $c);

        $asserter->assertProperty('tags', array($tags['special'], $tags['extremlyLoud']), \Psc\Code\Test\EntityAsserter::TYPE_COLLECTION);
      }
    );
    
    $tests[2] = array('assert => Properties', TRUE,
      function ($asserter, $row, $tags) {
        $properties = array(
          'label'=>NULL,
          'number'=>'psc0822387',
          'content'=>'[Programmierer schreiend] So kann ich nicht arbeiten!',
          
          // das hier geht kaputt: bigObject Warning
          //'tags'=>array($tags['special'], $tags['extremlyLoud'])
        );
        $asserter->assert($properties, \Psc\Code\Test\EntityAsserter::TYPE_SAME);
        $asserter->assert($properties, \Psc\Code\Test\EntityAsserter::TYPE_EQUALS);

        // das geht
        $asserter->assertProperty('tags', array($tags['special'], $tags['extremlyLoud']), \Psc\Code\Test\EntityAsserter::TYPE_COLLECTION);
      }
    );
    
    return $tests;
  }
}
?>