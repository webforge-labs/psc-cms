<?php

namespace Psc\CMS;

use Psc\CMS\ComponentsForm;

/**
 * @group class:Psc\CMS\ComponentsForm
 */
class ComponentsFormTest extends \Psc\Code\Test\Base {
  
  protected $form;

  public function setUp() {
    $this->chainClass = 'Psc\CMS\ComponentsForm';
    parent::setUp();
    $this->form = $this->createComponentsForm();
  }
  
  public function testConstruct() {
    // generate random id
    $form = new ComponentsForm();
    $this->assertGreaterThan(0, mb_strlen($form->getFormId())); // random erzeugte
    
    // übergebene id
    $form = new ComponentsForm('my-nice-unique-id');
    $this->assertEquals('my-nice-unique-id',$form->getFormId());
    
    return $form;
  }
  
  public function createComponentsForm() {
    return new ComponentsForm();
  }
  
  protected function createComponent($type) {
    if ($type === 'text') {
      return new \Psc\UI\Component\TextField();
    } elseif ($type === 'smallint') {
      return new \Psc\UI\Component\TextField();
    } else {
      return new \Psc\UI\Component\TextField();
    }
  }

  public function testComponentsCollection_AddCountAndGet() {
    $text = $this->createComponent('text');
    $smallint = $this->createComponent('smallint');
    $this->assertInstanceOfCollection($components = $this->form->getComponents());
    $this->assertCount(0, $components);
    
    $this->assertChainable($this->form->addComponent($text));
    $this->assertChainable($this->form->addComponent($smallint));
    
    $this->assertSame($text, $this->form->getComponent(0));
    $this->assertSame($smallint, $this->form->getComponent(1));
    
    $this->assertCount(2, $components);
    $this->assertEquals(array($text, $smallint), $components->toArray());
  }
  
  public function testGetComponentByFormName() {
    $this->assertNull($this->form->getComponentByFormName('importanttext'));
    $this->form->addComponent($this->createComponent('text')->setFormName('importanttext'));
    $this->assertInstanceOf('Psc\CMS\Component',$this->form->getComponentByFormName('importanttext'));
  }
  
  public function testsortComponentsBy() {
    $text = $this->createComponent('text')->setFormName('importanttext');
    $small = $this->createComponent('smallint')->setFormName('smallnumber');
    $other = $this->createComponent('other')->setFormName('otherfield');
    $this->form->addComponent($text);
    $this->form->addComponent($small);
    $this->form->addComponent($other);
    
    $this->form->sortComponentsBy('formName');
    $this->assertEquals(array($text, // i mportanttext
                              $other, // o therfield
                              $small // s mallnumber
                             ),
                        $this->form->getComponents()->toArray()
                       );
  }
  
  /**
   * @expectedException Psc\Code\WrongValueException
   * @dataProvider provideSortComponentByExcption
   */
  public function testSortComponentsByException($val) {
    $this->form->sortComponentsBy($val);
  }
  
  public static function provideSortComponentByExcption() {
    return array(array('name'),
                 array('value'),
                 array('type')
                );
  }
  
  
  public function testCreateTypeSelect() {
    $c = 'Psc\CMS\TestStatus';
    \Psc\PSC::getProject()->getModule('Doctrine')->registerType($c);
    
    $html = $this->form->createTypeSelect($c, 'mailed');
    
    $options = array(
      'discovered'=>'Discovered',
      'mailed'=>'Mailed',
      'scheduled'=>'Scheduled',
      'downloaded'=>'Downloaded',
      'moved'=>'Moved',
      'finished'=>'Finished'
    );
    
    $this->test->formSelect($html, 'TestStatus', 'testStatus', 'mailed', $options);
  }
  
  public function testPropertySorting() {
    extract($this->createPersonFormComponents());
    $this->form->sortComponents(array('firstName','name','birthday','yearKnown'));
    
    $this->assertEntityCollectionSame(
        new \Psc\Data\ArrayCollection(array($firstName, $name, $birthday, $yearKnown)),
        $this->form->getComponents(),
        'sortedComponents'
    );
    
  }
  
  public function testPropertySorting_MissingComponentsInOrder() {
    $this->form->addComponent($missing1 = $this->createComponent('text')->setFormName('missing1'));
    extract($this->createPersonFormComponents());
    $this->form->addComponent($missing2 = $this->createComponent('text')->setFormName('missing2'));
    
    $this->form->sortComponents(array('firstName','name','yearKnown'));

    $this->assertEntityCollectionSame(
        new \Psc\Data\ArrayCollection(array($firstName, $name, $yearKnown, $missing1, $birthday, $missing2)),
        $this->form->getComponents(),
        'sortedComponents'
    );
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testPropertySorting_wrongParam() {
    $this->form->sortComponents('falsch');
  }
    
  protected function createPersonFormComponents() {
    $birthday = $this->createComponent('other')->setFormName('birthday');
    $yearKnown = $this->createComponent('other')->setFormName('yearKnown');
    $firstName = $this->createComponent('smallint')->setFormName('firstName');
    $name = $this->createComponent('text')->setFormName('name');
    
    $this->form->addComponent($birthday);
    $this->form->addComponent($yearKnown);
    $this->form->addComponent($firstName);
    $this->form->addComponent($name);
    return compact('birthday', 'yearKnown', 'firstName', 'name');
  }
}

class TestStatus extends \Psc\Doctrine\EnumType {
  
  const DISCOVERED = 'discovered'; // wurde im stream gefunden
  const MAILED = 'mailed'; // wurde im stream gefunden und gemailed
  const SCHEDULED = 'scheduled';   // wurde zum download vorgemerkt, weil die Episode nicht vorhanden war
  const DOWNLOADED = 'downloaded'; // wurde scheduled und dann ausgewählt
  const MOVED = 'moved'; // wurde heruntergeladen und in die Verzeichnisstruktur eingeordnet
  const FINISHED = 'finished'; // wurde heruntergeladen und ist komplett fertig (auch mit subs)
  
  protected $name = 'PscCMSTestStatus';
  protected $values = array(self::DISCOVERED, self::MAILED, self::SCHEDULED, self::DOWNLOADED, self::MOVED, self::FINISHED);
  
  public static function instance() {
    return self::getType('PscCMSTestStatus');
  }
}
?>