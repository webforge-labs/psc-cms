<?php

namespace Psc\CMS;

use Psc\CMS\ComponentMapper;

/**
 * @group class:Psc\CMS\ComponentMapper
 */
class ComponentMapperTest extends \Psc\Code\Test\Base {
  
  protected $mapper;

  public function setUp() {
    $this->chainClass = 'Psc\CMS\ComponentMapper';
    parent::setUp();
    $this->mapper = $this->createComponentMapper();
    $this->dumper = new \Psc\Code\Event\Dumper($this->mapper);
  }

  public function assertPreConditions() {
    $this->assertInstanceOf('Psc\CMS\ComponentMapper',$this->mapper);
  }
  
  /**
   * @dataProvider provideTestMapping
   */
  public function testMapping($expectedComponent, $type) {
    $component = $this->mapper->inferComponent($type); 
    
    $this->assertInstanceOf('Psc\CMS\Component', $component, 'Mapper gibt kein Objekt des richtigen Interfaces zurueck');
    $this->assertEquals($expectedComponent, $component->getComponentName());
    
    $this->assertCount(2, $events = $this->dumper->getEvents(), 'Es wurden nicht 2 Events dispatched');
    // created muss zuerst kommen
    $this->assertEquals(ComponentMapper::EVENT_COMPONENT_CREATED, $events[0]->getIdentifier());
    $this->assertEquals(ComponentMapper::EVENT_COMPONENT_MAPPED, $events[1]->getIdentifier());
  }
  
  public static function provideTestMapping() {
    $tests = array();
    
    $test = function ($type, $expectedComponent) use (&$tests) {
      $type = \Psc\Data\Type\Type::create($type);
      $tests[] = array($expectedComponent, $type);
    };
    
    $test('String', 'TextField');
    $test('Email', 'EmailField');
    $test('DateTime', 'DateTimePicker');
    $test('Date', 'DatePicker');
    
    // Mapped Component
    $test('SmallInteger', 'SmallIntegerField');
    $test('Birthday', 'BirthdayPicker');
    
    return $tests;
  }
  
  /**
   * @expectedException Psc\CMS\NoComponentFoundException
   */
  public function testMappingException() {
    $type = $this->getMock('Psc\Data\Type\Type');
    
    $type->expects($this->any())
        ->method('getName')
        ->will($this->returnValue('DieserNameGibtEsBestimmtNichtImCMSWeilerZuDummist'));
      
    $this->testMapping('null', $type);
  }

  public function createComponentMapper() {
    return new ComponentMapper();
  }
}
?>