<?php

namespace Psc\FE;

/**
 * @group class:Psc\FE\Helper
 */
class HelperTest extends \Psc\Code\Test\Base {
  
  protected $helper;
  
  public function setUp() {
    $this->chainClass = 'Psc\FE\Helper';
    parent::setUp();
  }
  
  /**
   * @dataProvider listStringsParams
   */
  public function testListStrings(Array $array, $expectedString, $andsep, $sep, $print) {
    $this->assertEquals(
      $expectedString,
      \Psc\FE\Helper::listStrings($array, $sep, $andsep, $print)
    );
  }

  /**
   * @dataProvider listObjectsParams
   */
  public function testListObjects(\Psc\Data\ArrayCollection $collection, $expectedString, $andsep, $sep, $print) {
    $this->assertEquals(
      $expectedString,
      \Psc\FE\Helper::listObjects($collection, $sep, 'label', $andsep, $print)
    );
  }
  
  public static function listStringsParams() {
    $tests = array();
    
    $test = function(Array $array, $expectedString, $andsep = 'und', $print = NULL) use (&$tests) {
      $tests[] = array($array, $expectedString, $andsep, ', ', $print);
    };
    
    self::initTestParams($test);
    
    return $tests;
  }

  public static function listObjectsParams() {
    $tests = array();
    
    $test = function(Array $array, $expectedString, $andsep = 'und', $print = NULL) use (&$tests) {
      // mache aus allen strings ein object
      $array = array_map(function ($string) {
        return new TestCollectionObject(rand(0,20), $string);
      }, $array);
      
      $collection = new \Psc\Data\ArrayCollection($array);
      $tests[] = array($collection, $expectedString, $andsep, ', ', $print);
    };
    
    self::initTestParams($test);
    
    return $tests;
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testListObjects_InvalidGetterParam() {
    \Psc\FE\Helper::listObjects(new \Psc\Data\ArrayCollection(array(new TestCollectionObject(2,'test'))),
                                ', ',
                                array('label') // <= das ist falsch
                                );
  }
  
  public static function initTestParams($test) {
    // with andsep
    $test(array('item1','item2','item3'),
          'item1, item2 und item3',
          ' und '
         );
    $test(array('item1','item2'),
          'item1 und item2',
          ' und '
         );
    $test(array('item1'),
          'item1',
          'und'
         );
    
    // without andsep
    $test(array('item1','item2','item3'),
          'item1, item2, item3',
          NULL
         );
    $test(array('item1'),
          'item1',
          NULL
         );
    
    // empty
    $test(array(), '');


    // with closure
    $test(array('item1','item2','item3'),
          '[item1], [item2] ooouu [item3]',
          ' ooouu ',
          function ($item) { return sprintf('[%s]',$item); }
         );
  }
}

class TestCollectionObject {
  
  protected $id;
  protected $label;
  
  public function __construct($id, $label) {
    $this->id = $id;
    $this->label = $label;
  }
  
  /**
   * @param string $id
   * @chainable
   */
  public function setId($id) {
    $this->id = $id;
    return $this;
  }

  /**
   * @return string
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param string $label
   * @chainable
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }
}
?>