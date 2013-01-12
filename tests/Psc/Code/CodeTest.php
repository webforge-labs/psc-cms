<?php

namespace Psc\Code;

use Psc\Code\Code,
    Psc\PSC,
    Webforge\Common\System\Dir,
    Webforge\Common\System\File
;

/**
 * @covers Psc\Code\Code
 * @group class:Psc\Code\Code
 */
class CodeTest extends \Psc\Code\Test\Base {
  
  protected $c = '\Psc\Code\Code';
  
  public function testDeprecated() {
    $this->assertException('Psc\DeprecatedException', function () { Code::callback(NULL,NULL); });
    $this->assertException('Psc\DeprecatedException', function () { Code::eval_callback(array(),array()); });
  }
  
  /**
   * @dataProvider provideCastId
   */
  public function testCastId($expected, $id, $default = NULL) {
    $this->assertEquals($expected, Code::castId($id, $default));
  }
  public static function provideCastId() {
    $test = function () { return func_get_args(); };
    return array(
      $test(7, 7),
      $test('def', 0, 'def'),
      $test(1, 1, 0),
      $test(0, 0, 0),
      $test(7, '7'),
      $test('def', '7s', 'def'),
      $test('def', "s7", 'def')
    );
  }
  
  public function testGetClassName() {
    if (\Psc\PSC::getProject() !== 'psc-laptop' && \Psc\PSC::getProject() !== 'psc-desktop') {
      $this->markTestSkipped('kein bekanntes BaseDir definiert');
    }
    $file = new File('D:\www\psc-cms\Umsetzung\base\src\Psc\Code\Generate\GClass.php');
    $dir = new Dir('D:\www\psc-cms\Umsetzung\base\src\\');
    
    $this->assertEquals('\Psc\Code\Generate\GClass',Code::mapFileToClass($file,$dir));
    
    $file = new File('D:\www\psc-cms\Umsetzung\base\src\Psc\PSC.php');
    $dir = new Dir('D:\www\psc-cms\Umsetzung\base\src\\');
    $this->assertEquals('\Psc\PSC',Code::mapFileToClass($file,$dir));
  }
  
  /**
   * @group gn
   */
  public function testGetClassName_underscoreStyle() {
    if (\Psc\PSC::getProject() !== 'psc-laptop' && \Psc\PSC::getProject() !== 'psc-desktop') {
      $this->markTestSkipped('kein bekanntes BaseDir definiert');
    }
    $file = new File('D:\www\psc-cms\Umsetzung\base\src\Psc\Code\Generate\GClass.php');
    $dir = new Dir('D:\www\psc-cms\Umsetzung\base\src\\');
    
    $this->assertEquals('Psc_Code_Generate_GClass', Code::mapFileToClass($file, $dir, '_'));

    $file = new File('D:\www\psc-cms\Umsetzung\base\src\PHPWord\PHPWord.php');
    $dir = new Dir('D:\www\psc-cms\Umsetzung\base\src\PHPWord\\');
    
    $this->assertEquals('PHPWord', Code::mapFileToClass($file, $dir, '_'));
  }
  
  public function testNamespaceToPath_withoutDirParam() {
    $ds = DIRECTORY_SEPARATOR;
    $this->assertEquals(getcwd().$ds.'Psc'.$ds.'Code'.$ds.'Generate'.$ds, (string) Code::namespaceToPath('\Psc\Code\Generate')->resolvePath());
  }

  /**
   * @depends testNamespaceToPath_withoutDirParam
   */
  public function testMapClassToFile_withoutDirParam() {
    $ds = DIRECTORY_SEPARATOR;
    $this->assertEquals(getcwd().$ds.'Psc'.$ds.'Code'.$ds.'Generate'.$ds.'myClass.php', (string) Code::mapClassToFile('\Psc\Code\Generate\myClass')->resolvePath());
  }
  
  public function testCastGetter_doesntCastClosures() {
    $c = function ($nt) { };
    $this->assertInstanceOf('Closure',Code::castGetter($c));
    $this->assertSame($c, Code::castGetter($c));
  }

  public function testCastSetter_doesntCastClosures() {
    $c = function ($nt) { };
    $this->assertInstanceOf('Closure',Code::castSetter($c));
    $this->assertSame($c, Code::castSetter($c));
  }
  
  public function testCastGetter() {
    $object = new TestingClass('v1','v2');

    // pre
    $this->assertEquals('v1',$object->getProp1());
    $this->assertEquals('v2',$object->getProp2());
    
    // test 
    $getter = Code::castGetter('prop1');
    $this->assertEquals('v1',$getter($object));
    $getter = Code::castGetter('prop2');
    $this->assertEquals('v2',$getter($object));

    // test mit get
    $getter = Code::castGetter('getProp1');
    $this->assertEquals('v1',$getter($object));
    $getter = Code::castGetter('getProp2');
    $this->assertEquals('v2',$getter($object));
  }

  public function testCastSetter() {
    $object = new TestingClass('v1','v2');

    // pre
    $this->assertEquals('v1',$object->getProp1());
    $this->assertEquals('v2',$object->getProp2());
    
    // test
    $setter = Code::castSetter('prop1');
    $setter($object,'setv1');
    $this->assertEquals('setv1',$object->getProp1());
    
    $setter = Code::castSetter('prop2');
    $setter($object,'setv2');
    $this->assertEquals('setv2',$object->getProp2());

    $setter = Code::castSetter('setProp1');
    $setter($object,'setv1');
    $this->assertEquals('setv1',$object->getProp1());
    
    $setter = Code::castSetter('setProp2');
    $setter($object,'setv2');
    $this->assertEquals('setv2',$object->getProp2());
  }
  
  public function testDValue() {
    $value = 'eins';
    $this->assertEquals('eins', Code::dvalue($value,
                                             'eins','zwei','drei'));
    $this->assertEquals('eins',$value); // value as before
    
    $value = NULL;
    $this->assertEquals('eins', Code::dvalue($value,
                                             'eins','zwei','drei'));
    $this->assertEquals('eins',$value); // modified
    
    $value = 'notexistent';
    $this->assertEquals('eins', Code::dvalue($value,
                                             'eins','zwei','drei'));
    $this->assertEquals('eins',$value); // modified
    
    $value = 'zwei';
    $this->assertEquals('zwei', Code::dvalue($value,
                                             'eins','zwei','drei'));
    $this->assertEquals('zwei',$value); // value as before
    
    $value = 'drei';
    $this->assertEquals('drei', Code::dvalue($value,
                                             'eins','zwei','drei'));
    $this->assertEquals('drei',$value); // value as before
  }
  
  public function testValue() {
    $ex = 'Psc\Code\WrongValueException';
    $this->assertEquals('eins', Code::value('eins',
                                            'eins','zwei','drei'));
    $this->assertEquals('zwei', Code::value('zwei',
                                             'eins','zwei','drei'));
    $this->assertEquals('drei', Code::value('drei',
                                             'eins','zwei','drei'));
    $this->assertException($ex, function () {
      Code::value(NULL, 'eins','zwei','drei');
    });
    $this->assertException($ex, function () {
      Code::value('notexistent', 'eins','zwei','drei');
    });
  }

  public function testGetNamespace_root() {
    $this->assertEquals(NULL, Code::getNamespace('\stdClass'));
    $this->assertEquals(NULL, Code::getNamespace('stdClass'));
  }
  
  /**
   * @dataProvider provideTestExpandNamespace
   * @group expandnamespace
   */
  public function testExpandNamespace($expectedClass, $candidate, $expandNamespace) {
    $this->assertEquals($expectedClass, Code::expandNamespace($candidate, $expandNamespace));
  }
  
  public static function provideTestExpandNamespace() {
    $tests = array();
    $test = function ($class, $namespace, $expected) use (&$tests) {
      $tests[] = array($expected, $class, $namespace);
    };
    
    $test('\stdClass', 'Psc\UI\Component', '\stdClass');
    $test('stdClass', 'Psc\UI\Component', 'Psc\UI\Component\stdClass');
    $test('An\FQN\Class', 'Psc\UI\Component', 'An\FQN\Class');
    $test('\An\ABS\Class', 'Psc\UI\Component', '\An\ABS\Class');
    
    $test('\An\ABS\Class', NULL, '\An\ABS\Class');
    $test('className', NULL, '\className');
    $test('blubb\className', NULL, 'blubb\className');
    
    return $tests;
  }
  
  public function testGetMemoryUsageAcceptance() {
    $this->assertRegexp('/[0-9]+\.[0-9]+ MB/',Code::getMemoryUsage());
  }
  
  public function testTraversableTrues() {
    $this->assertTrue(Code::isTraversable(array('blubb')));
    $this->assertTrue(Code::isTraversable((object) array('blubb')));
    $this->assertTrue(Code::isTraversable(new \Psc\Data\ArrayCollection(array('blubb'))));
  }
}

// in dieser Klasse ist alles korrekt
class TestingClass {
  
  protected $prop1;
  protected $prop2;
  
  public function __construct($p1Value, $p2Value) {
    $this->prop1 = $p1Value;
    $this->prop2 = $p2Value;
  }
  
  public function getProp1() {
    return $this->prop1;
  }
  
  public function setProp1($p1Value) {
    $this->prop1 = $p1Value;
    return $this;
  }

  public function getProp2() {
    return $this->prop2;
  }
  
  public function setProp2($p2Value) {
    $this->prop2 = $p2Value;
    return $this;
  }
}

?>