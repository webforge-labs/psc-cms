<?php

namespace Psc\CMS;

use Psc\CMS\EntityFormPanel;
use Psc\Doctrine\ModelCompiler;
use Psc\Code\Generate\GClass;
use Psc\DateTime\DateTime;

class EntityFormPanelTest extends \Psc\Doctrine\DatabaseTest {

  public function setUp() {
    $this->con = 'tests';
    parent::setUp();
    $this->chainClass = 'Psc\CMS\EntityFormPanel';
    $this->compilePerson();
  }
  
  public function compilePerson() {
    $modelCompiler = new ModelCompiler();
    extract($modelCompiler->getClosureHelpers());
    $modelCompiler->setOverwriteMode(TRUE);
    
    $entityBuilder = $modelCompiler->compile(
      $entity(new GClass('Psc\Doctrine\TestEntities\Person')),
        $defaultId(),
        $property('name', $type('String')),
        $property('firstName', $type('String')),
        $property('email', $type('Email')),
        $property('birthday', $type('Birthday')),
      $constructor(
        $argument('name'),
        $argument('email', NULL),
        $argument('firstName', NULL),
        $argument('birthday', NULL)
      )
    );

    
    //print $entityBuilder->getWrittenFile();
    $this->installEntity('Psc\Doctrine\TestEntities\Person');
  }
  
  protected function createPanel($entity = NULL) {
    if (!isset($entity)) {
      $entity = new \Psc\Doctrine\TestEntities\Person('Scheit');
      $entity->setFirstName('Philipp');
      $entity->setBirthday(new \Psc\DateTime\DateTime('21.11.1984'));
      $entity->setEmail('p.scheit@ps-webforge.com');
    }
    
    $panel = new EntityFormPanel('Person bearbeiten', new EntityForm($entity));
    return $panel;
  }

  public function testAPIAcceptance() {
    $panel = $this->createPanel();
    
    // erstellt alle Felder für Person automatisch
    // Reihenfolge mehr oder weniger beliebig
    $panel->setWhitelistProperties(array('name','firstName','birthday','email'));
    $panel
      ->label('firstName','Vorname')
      ->label('birthday','Geburtstag')
    ;
    
    $panel->createComponents();
    
    $form = $panel->getForm();
    
    $this->assertCount(4, $form->getComponents());
    // TextField
    // TextField
    // BirthdayPicker
    // EmailField
    
    $html = $panel->layout();
    $this->test->formInput($html, 'Name', 'name', 'Scheit', 'text');
    $this->test->formInput($html, 'Vorname', 'firstName', 'Philipp', 'text');
    $this->test->formInput($html, 'E-Mail', 'email', 'p.scheit@ps-webforge.com', 'text');
    $this->test->formInput($html, 'Geburtstag', 'birthday', '21.11.1984', 'text');
  }
  
  public function testCreateComponent() {
    $panel = $this->createPanel();
    $component = $panel->createComponent('birthday', $this->getType('Birthday'));
    
    $this->assertInstanceOfComponent('BirthdayPicker', $component);
    $this->assertEquals('birthday',$component->getFormName());
    $this->assertEquals(new DateTime('21.11.1984'),$component->getValue());
    $this->assertEquals('21.11.1984',$component->getFormValue()->format('d.m.Y'));
    $this->assertEquals('Birthday',$component->getFormLabel()); // auto-label vom labeler
  }
  
  public function testConstruct_RegistersAndCallsEntityListener_onComponentCreated() {
    /* alle details zu testen macht hier keinen sinn. deshalb testen wir ob das Event im Endeffekt beim AbstractEntity ankommt
      von da an deckt der test vom AbstractEntity weiter ab */
    
    $entity = $this->getMock('Psc\Doctrine\TestEntities\Person', array('onComponentCreated'), array('Scheit'));
    $entity->setFirstName('Philipp');
    $entity->setBirthday(new \Psc\DateTime\DateTime('21.11.1984'));
    $entity->setEmail('p.scheit@ps-webforge.com');
    
    $panel = $this->createPanel($entity);
    
    $entity->expects($this->atLeastOnce())->method('onComponentCreated')
           ->with($this->isInstanceOf('Psc\CMS\Component'),
                  $this->equalTo($panel),
                  $this->isInstanceOf('Psc\Code\Event\Event')
                 );
           
    $panel->createComponents();
  }

  public function testSortComponents() {
    $panel = $this->createPanel();
    $panel->setWhitelistProperties(array('name','firstName','birthday','email'));
    $panel->createComponents();
    
    $form = $panel->getForm();

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