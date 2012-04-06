<?php

namespace Psc\Form;

use Psc\Data\SetMeta;
use Psc\Data\Set;
use Psc\DateTime\DateTime;
use Psc\Doctrine\TestEntities\Person;
use Psc\CMS\EntityFormPanel;
use Psc\CMS\EntityForm;

/**
 * @TODO onValidation für validateSet testen
 */
class ComponentsValidatorTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Form\SetValidator';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $person = new Person('Scheit', 'p.scheit@ps-webforge.com', 'Philipp', DateTime::create('21.11.1984'));
    $formPanel = new EntityFormPanel('Person bearbeiten',new EntityForm($person));
    $formPanel->createComponents();
    
    $this->validator = new ComponentsValidator(
      new Set(array('id'=>'17',
                    'birthday'=>'21.11.1984',
                    'name'=>'Scheit',
                    'firstName'=>'Philipp ',
                    'email'=>'p.scheit@ps-webforge.com'
                    ),
              $person->getSetMeta()
            ),
      $formPanel->getEntityForm()->getComponents()
    );

    $this->validator->validateSet(); // sollte keine Exceptions schmeissen
    
    $set = $this->validator->getSet();
    $this->assertEquals(17, $set->get('id'));
    $this->assertEquals('21.11.1984', $set->get('birthday')->format('d.m.Y'));
    $this->assertEquals('Scheit', $set->get('name'));
    $this->assertEquals('Philipp', $set->get('firstName'));
    $this->assertEquals('p.scheit@ps-webforge.com', $set->get('email'));
  }
  
}
?>