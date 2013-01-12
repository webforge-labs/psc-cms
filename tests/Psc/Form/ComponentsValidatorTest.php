<?php

namespace Psc\Form;

use Psc\Data\SetMeta;
use Psc\Data\Set;
use Psc\DateTime\Date;
use Psc\Doctrine\TestEntities\Person;
use Psc\CMS\EntityFormPanel;
use Psc\CMS\EntityForm;

/**
 * @group class:Psc\Form\ComponentsValidator
 * @TODO onValidation für validateSet testen
 */
class ComponentsValidatorTest extends \Psc\Code\Test\Base {
  
  protected $validator, $failingValidator;
  
  public function setUp() {
    $this->chainClass = 'Psc\Form\SetValidator';
    parent::setUp();
    $this->validator = $this->createFixture();
    $this->failingValidator = $this->createFixture(array('birthday'=>'blubb',
                                                      'email'=>'wrong'
                                                     )
                                                );
  }
  
  public function testAcceptance() {
    $this->validator->validateSet(); // sollte keine Exceptions schmeissen
    
    $set = $this->validator->getSet();
    $this->assertEquals(17, $set->get('id'));
    $this->assertEquals('21.11.1984', $set->get('birthday')->format('d.m.Y'));
    $this->assertEquals('Scheit', $set->get('name'));
    $this->assertEquals('Philipp', $set->get('firstName'));
    $this->assertEquals('p.scheit@ps-webforge.com', $set->get('email'));
  }
  
  public function testExceptionListThrowing() {
    $this->setExpectedException('Psc\Form\ValidatorExceptionList');
    $this->failingValidator->setExceptionList(TRUE);
    $this->failingValidator->validateSet();
  }

  public function testNormalValidatorExceptionThrowing() {
    $this->setExpectedException('Psc\Form\ValidatorException');
    $this->failingValidator->validateSet();
  }
  
  protected function createFixture(Array $data = array()) {
    $person = new Person('Scheit', 'p.scheit@ps-webforge.com', 'Philipp', Date::create('21.11.1984'));
    $formPanel = new EntityFormPanel('Person bearbeiten',new EntityForm($person, $this->getEntityMeta('Psc\Doctrine\TestEntities\Person')->getSaveRequestMeta($person)));
    $formPanel->createComponents();
    
    $validator = new ComponentsValidator(
      new Set(array_merge(
                array('id'=>'17',
                      'birthday'=>'21.11.1984',
                      'name'=>'Scheit',
                      'firstName'=>'Philipp ',
                      'email'=>'p.scheit@ps-webforge.com',
                      'yearKnown'=>'true'
                      ),
                $data
              ),
              $person->getSetMeta()
            ),
      $formPanel->getEntityForm()->getComponents()
    );
    
    return $validator;
  }
  
  public function testPostValidations() {
    $cb = new \Psc\Code\Callback(function (ComponentsValidator $validator, Array $components) {

    });
    
    $this->validator->addPostValidation($cb);
    $this->validator->validateSet();
    
    $this->assertTrue($cb->wasCalled());
  }
}
?>