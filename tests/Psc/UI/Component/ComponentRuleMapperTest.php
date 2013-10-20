<?php

namespace Psc\UI\Component;

use Webforge\Types\Type;
use Psc\CMS\ComponentMapper;

/**
 * @group class:Psc\UI\Component\ComponentRuleMapper
 */
class ComponentRuleMapperTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\Component\ComponentRuleMapper';
    parent::setUp();
    $this->mapper = new ComponentRuleMapper();
  }
  

  /**
   * @dataProvider provideMappings
   */
  public function testMapping($expectedRule, $type) {
    $this->assertEquals($expectedRule, $this->mapper->getRule($type));
  }

  public static function provideMappings() {
    $ruleMapper = new ComponentRuleMapper();
    $tests = array();
    
    $test = function ($component, $rule) use (&$tests, $ruleMapper) {
      $tests[] = array($ruleMapper->createRule($rule), $component);
    };
    
    // delegates to typeRuleMapper
    $component = new IntegerField();
    $component->setType(Type::create('Id'));
    $test($component, 'Psc\Form\IdValidatorRule');

    $component = new TextField();
    $component->setType(Type::create('String'));
    $test($component, 'Psc\Form\NesValidatorRule');
    
    // eigene Componenten-Rules
    $component = new DatePicker();
    $component->init();
    $test($component, 'Psc\Form\DateValidatorRule');
    
    return $tests;
  }
  
  public function createComponentRuleMapper() {
    return new ComponentRuleMapper();
  }
}
