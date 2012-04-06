<?php

namespace Psc\CMS;

use Psc\CMS\EntityForm;

class EntityFormTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityForm';
    parent::setUp();
  }

  public function testHTML() {
    $entity = new \Psc\Doctrine\TestEntities\Person('Mr Roboto');
    $entity->setIdentifier($id = 17);
    
    $form = new EntityForm($entity);
    
    $html = $form->open();
    $html .= $form->close();
    
    // entityform überhaupt da
    $form = $this->test->css('.psc-cms-ui-form.entity-form', $html)
         ->count(1, 'Im HTML Output ist kein <form> Element vorhanden')
         ->getjQuery();
    
    // submitted
    $this->test->css('input[type="hidden"][name="submitted"][value="true"]', $form)
         ->count(1, 'input type hidden submitted value true ist nicht da');

    $input = $this->test->css('input[name="identifier"][type="hidden"]', $form->html())
         ->count(1, 'identifier ist nicht im formular')
         ->hasAttribute('value', $id)
         ->getjQuery()
      ;
    
    // @TODO noch die Buttons?
  }

  public function createEntityForm() {
    return new EntityForm();
  }
}
?>