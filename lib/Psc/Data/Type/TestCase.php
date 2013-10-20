<?php

namespace Psc\Data\Type;

use Psc\CMS\ComponentMapper;
use Psc\Code\Code;
use Webforge\Types\Type as WebforgeType;
use Webforge\Types\CodeExporter;

class TestCase extends \Psc\Code\Test\Base {
  
  protected function assertTypeMapsComponent($class, WebforgeType $type, $mapper = NULL) {
    $mapper = $mapper ?: new ComponentMapper;
    $this->assertInstanceOf('Psc\CMS\Component', $component = $mapper->inferComponent($type));
    
    if ($class !== 'any') {
      $class = Code::expandNamespace($class, 'Psc\UI\Component');
      $this->assertInstanceOf($class, $component);
    }
    return $component;
  }
  
  /**
   * Überprüft ob der vom CodeExporter exportierte PHP Code ausgeführt dasselbe ergebnis wieder type hat
   *
   * dies gewährleistet, dass der type so wie er instanziiert, dann exportiert und dann wieder ausgeführt wird derselbe ist wie die Instanz
   */
  protected function assertCodeExportEquals(WebforgeType $type) {
    $ce = new CodeExporter(new \Webforge\Common\CodeWriter());
    
    $phpCode = '$actualType = '.$ce->exportType($type).';';
    eval($phpCode);
    
    if (!isset($actualType)) {
      $this->fail('Evald Code verursachte einen Fehler: '.$phpCode);
    }
    $this->assertEquals($type, $actualType, 'PHPCode ist: '.$phpCode);
  }

  protected function assertObjectType($classFQN, WebforgeType $type) {
    $this->assertInstanceOf('Webforge\Types\ObjectType', $type);
    $this->assertEquals($classFQN, $type->getClassFQN(), 'Type ist ein ObjectType aber hat nicht die richtige Klasse');
  }
  
  protected function assertDocType($docType, WebforgeType $type) {
    $msg = 'DokumentationsType des Type '.$type.' ist nicht korrekt';
    $this->assertEquals($docType, $type->getName(WebforgeType::CONTEXT_DOCBLOCK), $msg);
    $this->assertEquals($docType, $type->getDocType(), $msg);
  }
}
