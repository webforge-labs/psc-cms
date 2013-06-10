<?php

namespace Psc\CMS;

use Psc\CMS\EntityFormPanel;
use Psc\Code\Generate\GClass;
use Psc\DateTime\Date;
use Psc\Doctrine\TestEntities\Person;

/**
 * @group class:Psc\CMS\EntityFormPanel
 */
class EntityFormPanelTest extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $panel;

  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityFormPanel';
    parent::setUp();
    
    $entity = new Person('Scheit');
    $entity->setFirstName('Philipp');
    $entity->setBirthday(new Date('21.11.1984'));
    $entity->setEmail('p.scheit@ps-webforge.com');
    
    $this->panel = new EntityFormPanel(
      'Person bearbeiten',
      $this->getTranslationContainer(),
      new EntityForm(
        $entity,
        $this->getEntityMeta('Psc\Doctrine\TestEntities\Person')->getSaveRequestMeta($entity)
      )
    );
  }

  public function testPanelWritesWhitelistedPropertiesIntoItsHTMLOutput() {
    // erstellt alle Felder für Person automatisch
    // Reihenfolge mehr oder weniger beliebig
    $this->panel->setWhitelistProperties(array('name','firstName','birthday','email'));
    $this->panel
      ->label('firstName','Vorname')
      ->label('birthday','Geburtstag')
    ;
    
    $this->panel->createComponents();
    $form = $this->panel->getForm();
    
    $this->assertCount(4, $form->getComponents());
    // TextField
    // TextField
    // BirthdayPicker
    // EmailField
    
    $html = $this->panel->html();
    $this->test->formInput($html, 'Name', 'name', 'Scheit', 'text');
    $this->test->formInput($html, 'Vorname', 'firstName', 'Philipp', 'text');
    $this->test->formInput($html, 'E-Mail', 'email', 'p.scheit@ps-webforge.com', 'text');
    $this->test->formInput($html, 'Geburtstag', 'birthday', '21.11.1984', 'text');
  }
  
  public function testCreateComponent() {
    $component = $this->panel->createComponent('birthday', $this->getType('Birthday'));
    
    $this->assertInstanceOfComponent('BirthdayPicker', $component);
    $this->assertEquals('birthday',$component->getFormName());
    $this->assertEquals(new Date('21.11.1984'),$component->getValue());
    $this->assertEquals('21.11.1984',$component->getFormValue()->format('d.m.Y'));
    $this->assertEquals('Birthday',$component->getFormLabel()); // auto-label vom labeler
  }
  
  public function testSortComponents() {
    $this->panel->setWhitelistProperties(array('name','firstName','birthday','email'));
    $this->panel->createComponents();
    
    $form = $this->panel->getForm();

    $this->assertEquals(array(
        $form->getComponentByFormName('name'),
        $form->getComponentByFormName('firstName'),
        $form->getComponentByFormName('birthday'),
        $form->getComponentByFormName('email'),
      ),
      $form->getComponents()->toArray()
    );

    $form->sortComponentsBy('formName'); // das ist natürlich im echten Leben echt sinnfrei
    
    $this->assertEquals(array(
        $form->getComponentByFormName('birthday'),
        $form->getComponentByFormName('email'),
        $form->getComponentByFormName('firstName'),
        $form->getComponentByFormName('name'),
      ),
      $form->getComponents()->toArray()
    );
  }  
}
?>