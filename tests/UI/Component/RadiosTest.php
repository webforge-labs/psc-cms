<?php

namespace Psc\UI\Component;

use Psc\UI\Component\Radios;

/**
 * @group component
 */
class RadiosTest extends TestCase {
  
  protected $vs;

  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\Radios';
    parent::setUp();
  }

  public function testHTML() {
    $html = $this->component->getHTML();
    
    $this->test->css('input[type="radio"][name="testName"]', $html)
      ->count(3);

    foreach ($this->vs as $name => $label) {
      $this->test->css(sprintf('input[type="radio"][name="testName"][value="%s"]',$name), $html)
        ->count(1,'Radio mit dem Namen: '.$name.' nicht im HTML gefunden');
    }
    
    $this->test->css('label', $html)->count(3);
  }

  public function createComponent() {
    $this->component = new Radios();
    $this->setFixtureValues();
    $this->component->setValues($this->vs = array('text'=>'Sprache','fx'=>'FX','song'=>'Lieder'));
    return $this->component;
  }
}
?>