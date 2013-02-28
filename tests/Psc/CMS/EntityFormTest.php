<?php

namespace Psc\CMS;

use Psc\CMS\EntityForm;

/**
 * @group class:Psc\CMS\EntityForm
 */
class EntityFormTest extends \Psc\Code\Test\HTMLTestCase {

  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityForm';
    parent::setUp();

    $this->entity = new \Psc\Doctrine\TestEntities\Person('Mr Roboto');
    $this->entity->setIdentifier($id = 17);
    $this->form = new EntityForm($this->entity, new RequestMeta('PUT', '/entities/persons/mr-roboto'));
  }

  public function testHTML() {
    $this->html = $this->form;
    
    // entityform überhaupt da
    $this->test->css('.psc-cms-ui-form.psc-cms-ui-entity-form')
         ->count(1, 'Im HTML Output ist kein <form> Element vorhanden');
    
    $this->test->css('form[action="/entities/persons/mr-roboto"]')
      ->count(1)
      ->hasAttribute('method','post') // das ist nicht die aus requestMeta, aber gewollt (browser kompatibel)
      ->test('input[name="X-Psc-Cms-Request-Method"]')
        ->count(1)
        ->hasAttribute('value','PUT');
    
    $this->assertContains('X-Psc-Cms-Request-Method', $this->form->getControlFields());
  }
  
  public function testGetEntity() {
    $this->assertSame($this->entity, $this->form->getEntity());
  }
  
  public function testEntityFormHasARevisionPerDefault() {
    $this->html = $this->form;
    
    $this->test->css('form')
      ->count(1)
      ->css('input[name="X-Psc-Cms-Revision"]')->count(1)->end()
    ;
  }
}
?>