<?php

namespace Psc\Code\Test;

use Psc\Doctrine\EntityDataRow;
use Psc\Code\Test\FormTester;
use Psc\Code\Test\FormTesterFrontend;
use Psc\Code\Test\FormTesterData;
use Psc\Code\Test\FormTesterHydrator;
use Psc\System\BufferLogger;
use Psc\URL\Request;

abstract class FormTestCase extends \Psc\Doctrine\DatabaseTest {
  
  protected $formTester;
  
  /* INTERFACE */
  /**
   * Wird einmal ausgeführt und gibt das HTML des Formulares zurück
   *
   * durch das HTML des Formulares wird das FormTesterFrontend erzeugt
   * @return string
   */
  abstract protected function getFormHTML();

  /**
   * Gibt die URL zurück an die der Request gesendet wird (mit den Formdaten als POST)
   * @param string|NULL $formAction wenn im formHTML gesetzt wird diese hier übergeben (diese dann absolute machen)
   * @return string
   */
  abstract protected function getFormURL($formAction = NULL);
  
  /**
   * Wird nach setUp() aufgerufen, sollte die Fixtures der Datenbank laden und das Schema updaten (wenn nötig)
   * @return void
   */
  abstract protected function setUpDatabase();
  
  /**
   * Gibt den EnityNamen des Elementes an für den der FormTest ist
   * @return string FQN des Entities oder der Shortname
   */
  abstract protected function getFormEntityName();
  
  /**
   * Gibt den Identifier zurück, den der aufgerufene Controller zurückgibt
   *
   * bei einem Insert-Test sollte dies hier die eingefügte ID sein.
   * Bei einem edit-test ist dies die ID die editiert wird
   * 
   * @return mixed Identifier vom Entity welches untersucht werden soll (in die Datenbank eingefügt wurde)
   */
  abstract protected function getFormEntityIdentifier();
  
  /**
   * Wird aufgerufen nach dem der Request gesendet wurde
   *
   * kleine Hilfsfunktion für assertResponseSuccess und assertResponseFailure
   * @param \Psc\URL\Response $response das Ergebnis, dass der Controller zurückgegeben hat
   * @param \Psc\URL\Request $request der Request, den der Controller bekommen hat
   */
  abstract protected function onControllerResponse(\Psc\URL\Response $response, \Psc\URL\Request $request);
  
  
  /**
   * Wird aufgerufen wenn der Test ein Success-Test ist
   *
   * wird anstatt von assertResponseFailure aufgerufen
   * wird nach onControllerResponse aufgerufen
   */
  abstract protected function assertResponseSuccess(\Psc\URL\Response $response, \Psc\URL\Request $request);
  
  /**
   * Wird aufgerufen wenn der Test ein Failure-Test ist
   *
   * wird anstatt von assertResponseSuccess aufgerufen
   * wird nach onControllerResponse aufgerufen
   */
  abstract protected function assertResponseFailure(\Psc\URL\Response $response, \Psc\URL\Request $request);
  
  /**
   * Wandelt die statischen Daten aus dem Datenprovider in Daten die mit dem Entity verglichen werden um
   */
  abstract protected function hydrateExpectedData(\Psc\Code\Test\FormTesterHydrator $hydrator, $actualEntity);
  
  /**
   * Alle Assertions für das Form Entity
   * 
   */
  abstract protected function assertFormEntity($actualEntity, \Psc\Code\Test\FormTesterData $data);
  

  /* INTERFACE */
  protected function doPREHATest(Array $formData, Array $expectedData) {
    $this->log('Starte PREHA-Test');
    $entity = $this->getEntityName($this->getFormEntityName());
    $this->log('EntityName: '.$entity);
    
    $this->log('Erstelle FormTester');
    $this->formTester = $this->createFormTester($entity);
    
    // Prepare
    $this->log('Prepare: erstelle Request aus den FormularDaten ($formData, $expectedData)');
    $data = $this->createFormTesterData($entity, $formData, $expectedData);
    $this->assertInstanceOf('Psc\Code\Test\FormTesterData',$data);
    $this->formTester->prepareRequest($data);
    
    // run
    $this->log('Run: rufe den Controller auf.');
    $this->log($this->formTester->getRequest()->debug());
    $response = $this->formTester->run();
    $this->log('Run: Ergebnis'.$response->debug());
    $this->log('rufe onControllerResponse auf');
    $this->onControllerResponse($response, $this->formTester->getRequest());
    $this->log('rufe assertResponseSuccess auf');
    $this->assertResponseSuccess($response, $this->formTester->getRequest());
    
    // erase from memory
    $this->em->clear();
    
    // hydrate
    $hydrator = $this->createFormTesterHydrator($entity, $data);
    $this->assertInstanceOf('Psc\Code\Test\FormTesterHydrator',$hydrator);    
    
    $entityObject = $this->hydrate($entity, $this->getFormEntityIdentifier());
    $this->hydrateExpectedData($hydrator, $entityObject);
    
    // assert
    $this->formTester->assert($hydrator->getData()); // ka was der hier machen kann? dynamisch?
    
    $this->assertFormEntity($entityObject, $hydrator->getData());
  }
  
  /**
   *
   * ein FailureTest sollte tatächlich nur "failen" und nicht noch zusätzliche Dinge überprüfen wie:
   * Exception_Type usw. Deshalb sollten FailureTests als Akzeptanztest mit vorsicht zu genießen sein,
   * denn sie können false-positive Tests erzeugen
   *
   * Es geht IRGENDWAS schief aber der Unit-Test sagt uns was schief geht
   */
  protected function doFailureTest(Array $formData) {
    $entity = $this->getEntityName($this->getFormEntityName());
    
    $this->formTester = $this->createFormTester($entity);
    
    // Prepare
    $data = $this->createFormTesterData($entity, $formData, array());
    $this->assertInstanceOf('Psc\Code\Test\FormTesterData',$data);
    $this->formTester->prepareRequest($data);
    
    // run + fail
    $response = $this->formTester->run();
    try {
      $this->onControllerResponse($response, $this->formTester->getRequest());
    } catch (\Psc\Code\Test\FailureException $e) {
      return $e; // das ist auch ok
    }
    
    try {
      $ass = $this->getNumAssertions();
      $this->assertResponseFailure($response, $this->formTester->getRequest());
    } catch (\Psc\Code\Test\FailureException $e) {
      return $e; // das ist auch ok
    }
    
    if ($ass <= $this->getNumAssertions()) {
      $this->fail('In assertResponseFailure wurden keine Assertions gemacht! Wenn der Test so okay ist, muss eine Psc\Code\TestFailureException in assertResponseFailure geschmissen werden');
    }
    
  }
  
  public function setUp() {
    parent::setUp();
    $this->logger = new BufferLogger();
    $this->logger->setPrefix('[FormTestCase]');
    
    $this->setUpDatabase();
  }

  protected function createFormTester($entity)  {
    $frontend = $this->createFormTesterFrontend();
    $this->assertInstanceof('Psc\Code\Test\FormTesterFrontend',$frontend); // checks für wenn die funktion überladen wird
    
    $request = $this->createFormTesterRequest($frontend);
    $this->assertInstanceOf('Psc\URL\Request',$request);
    
    $formTester = new FormTester($frontend, $request);
    
    return $formTester;
  }
  
  protected function createFormTesterData($entity, Array $form, Array $expected) {
    return new FormTesterData(new EntityDataRow($entity, $form), new EntityDataRow($entity, $expected));
  }
  
  protected function createFormTesterHydrator($entity, $data) {
    // @TODO mit den Metadaten von $entity könnten wir hier schon alle Doctrine-Hydrators setzen (die wir brauchen)
    
    return new FormTesterHydrator($data, $this->em);
  }
  
  /**
   * @return Psc\Code\Test\FormTesterFrontend
   */
  protected function createFormTesterFrontend() {
    $frontend = new FormTesterFrontend();
    $this->assertNotEmpty($html = $this->getFormHTML(),'getFormHTML gibt einen leeren String zurück');
    $frontend->parseFrom($html);
    return $frontend;
  }
  
  /**
   * @return Psc\URL\Request
   */
  protected function createFormTesterRequest($frontend) {
    $hostConfig = \Psc\PSC::getProjectsFactory()->getHostConfig();
    
    $this->assertStringStartsWith('http',$url = $this->getFormURL($frontend->getAction()),'getFormURL() muss eine absolute URL zurückgeben. PSC::getProject()->getBaseURL() ?');
    
    $request = new \Psc\URL\Request($url);
    $request->setAuthentication($hostConfig->req('cms.user'),$hostConfig->req('cms.password'),CURLAUTH_BASIC);
    
    foreach ($frontend->getHTTPHeaders() as $name => $value) {
      $request->setHeaderField($name, $value);
    }
    
    return $request;
  }
  
  /**
   * Hilfs-Closures für das erstellen der Testdaten
   *
   * usage:
   *
   *    http://wiki.ps-webforge.com/psc-cms:dokumentation:tests
   *
   * Closures: 
   *
   *    ===  $fields() ===
   *    setzt die Namen der Felder (und die Reihenfolge der Argumente für $formData und $expectedData)
   *
   *    === $formData() ===
   *    setzt die Daten für die Formular-Daten in der Reihenfolge wie $fields() angegeben hat
   * 
   *    === $expectedData(Array $data) ===
   *    setzt die erwarteten Daten in der Datenbank.
   *    hier muss ein assoziativer Array übergeben werden,
   *    denn wenn hier felder ausgelassen werden, werden diese mit den Daten aus $formData aufgefüllt
   *
   *    === $expect($field, $value) ===
   *    setzt ein Feld in $expectedData() (überschreibt vorige)
   *
   *    === $test($formData(), $expectedData(), $expect(), $expect(), $expect(), ...) ===
   *    als erstes muss die formData übergeben werden
   *    danach können n aufrufe von $expect() oder $expectedData() kommen
   * 
   * @return Closure[]  
   */ 
  public static function providerHelpers() {
    $fieldKeys = array();
    
    $fields = function () use (&$fieldKeys) {
      $fieldKeys = func_get_args();
    };
    
    $formData = function () use (&$fieldKeys) {
      $fieldData = \Psc\A::fillUp(func_get_args(), NULL, count($fieldKeys));
      
      return array_combine($fieldKeys, $fieldData);
    };
    
    $expectedData = $dbData = function (array $expectedData) {
      return $expectedData;
    };
    
    $expect = function ($field, $value) {
      $ret = array();
      $ret[$field] = $value;
      return $ret;
    };
    
    /*
     * @param $expected, ...
     */
    $test = function (Array $formData, Array $expected1 = array(), Array $expected2 = array()) {
      $args = array_slice(func_get_args(),1);
      
      $expected = array();
      foreach ($args as $expectedx) {
        $expected = array_merge($expected, $expectedx);
      }
      
      return array($formData, $expected);
    };
    
    return compact('test','fieldKeys','fields','formData','expect','expectedData','dbData');
  }
  
  protected function onNotSuccessfulTest(\Exception $e) {
    print $this->logger->toString();
    throw $e;
  }
  
  protected function log($msg) {
    $this->logger->writeln($msg);
    return $this;
  }
}
?>