<?php

namespace Psc\CMS;

use Psc\CMS\Labeler;

/**
 * @group class:Psc\CMS\Labeler
 */
class LabelerTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\CMS\Labeler';
    parent::setUp();
    $this->labeler = self::createLabeler();
  }

  public function testConstruct() {
    $this->assertChainable($this->labeler);
  }
  
  public function testGetCustomLabels() {
    $this->assertEquals(array('firstName'=>'Vorname',
                              'name'=>'Nachname',
                              'birthday'=>'geboren am'
                              ),
                        $this->labeler->getCustomLabels()
                       );
  }
  
  public function testGetCommonLabels() {
    $this->assertInternalType('array',$this->labeler->getCommonLabels());
  }
  
  /**
   * @dataProvider provideGetLabel
   */
  public function testGetLabel(Labeler $labeler, $identifier, $expectedLabel) {
    $this->assertEquals($expectedLabel, $labeler->getLabel($identifier));
  }
  
  // alder geht mir das mit dem static aufn Sack!
  public static function provideGetLabel() {
    $tests = array();
    $test = function ($identifier, $expectedLabel) use (&$labeler, &$tests) {
      $tests[] = array($labeler, $identifier, $expectedLabel);
    };
    
    $labeler = self::createLabeler();
    
    // our customLabels
    $test('firstName', 'Vorname');
    $test('name', 'Nachname');
    $test('birthday', 'geboren am');
    
    // our commonLabels
    $test('email','E-Mail');
    
    // guessed Label
    $test('address', 'Address');
    
    
    // empty labeler guesses more
    $labeler = new Labeler();
    
    // our now guessed labels
    $test('surName','Sur Name');
    $test('name', 'Name');
    $test('birthday', 'Birthday');
    
    // our commonLabels
    $test('email','E-Mail');
    $test('firstName', 'Vorname');
    
    return $tests;
  }
  
  public static function createLabeler() {
    $l = new Labeler();
    
    $l->addLabelMapping('firstName','Vorname');
    $l->addLabelMapping('name','Nachname');
    $l->addLabelMapping('birthday','geboren am');
    return $l;
  }
}
?>