<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\GParameter,
    Psc\Code\Generate\GMethod,
    Psc\Code\Generate\GFunction,
    Psc\Code\Generate\GProperty,
    Psc\Code\Generate\GClass,
    Psc\Code\Generate\ClassWriter,
    \Psc\System\System,
    \Psc\PSC,
    \Psc\A,
    \Psc\String AS S,
    \Psc\System\File
;
use ReflectionClass;
use stdClass;

/**
 * @group generate
 * @group class:Psc\Code\Generate\GClass
 * @group class:Psc\Code\Generate\GMethod
 * @group class:Psc\Code\Generate\GFunction
 * @group class:Psc\Code\Generate\ClassWriter
 * @group class:Psc\Code\Generate\GParameter
 */
class GenerationTest extends \Psc\Code\Test\Base {
  
  static $testClassCode;
  
  static $startLine = 0;
  static $endLine = 0;
  
  public static function setUpBeforeClass() {
    $reflection = new ReflectionClass(__NAMESPACE__.'\\TestClass');
    self::$startLine = $reflection->getStartLine();
    self::$endLine = $reflection->getEndLine();
    self::$testClassCode = NULL;
    
    /* GetCode as String */
    $file = new \SplFileObject($reflection->getFileName());
    $file->seek(self::$startLine-1);
    while ($file->key() < $reflection->getEndLine()) {
      self::$testClassCode .= $file->current();
      $file->next();
    }
    self::$testClassCode = rtrim(self::$testClassCode,"\n");
    unset($file);
  }
  
  public function testNewClass() {
    $class = new GClass();
    $class->setName('tiptoi\Entities\Page'); // da dieser Test in keinem NS ist
    
    $this->assertEquals('\tiptoi\Entities',$class->getNamespace());
    $this->assertEquals('Page',$class->getClassName());
    
    $class->addMethod(
                      GMethod::factory('addOID', array(
                                               GParameter::factory(
                                                                   '$oid',
                                                                   GClass::factory('OID')
                                                                   )
                                               ),
'if (!$this->oids->contains($oid)) {
  $this->oids->add($oid);
}

return $this;
'                                       )
                      );
    
    $class->setMethod(
                      GMethod::factory('removeOID', array(
                                               GParameter::factory(
                                                                   '$oid',
                                                                   GClass::factory('OID')
                                                                   )
                                               ),
'if ($this->oids->contains($oid)) {
  $this->oids->removeElement($oid);
}

return $this;
'                                     ));
    
    $class->addMethod(GMethod::factory('getOIDs', array(), 'return $this->oids;'));

    $classCode = <<< 'CLASS_CODE'
class Page {
  
  public function addOID(OID $oid) {
    if (!$this->oids->contains($oid)) {
      $this->oids->add($oid);
    }
    
    return $this;
  }
  
  public function removeOID(OID $oid) {
    if ($this->oids->contains($oid)) {
      $this->oids->removeElement($oid);
    }
    
    return $this;
  }
  
  public function getOIDs() {
    return $this->oids;
  }
}
CLASS_CODE;
//    file_put_contents('D:\fixture.txt', $classCode);
//    file_put_contents('D:\compiled.txt',$class->php());

    $this->assertEquals($classCode,$class->php());
  }
  
  /**
   * @TODO test interfaces und extends!
   */
  public function testClass() {
    
    $cr = "\n";
    $class = GClass::factory(__NAMESPACE__.'\\TestClass');
    
    $this->assertInstanceOf('Psc\Code\Generate\GMethod',$class->getMethod('method2'));
    $this->assertInstanceOf('Psc\Code\Generate\GProperty',$class->getProperty('prop1'));
    
    $gClass = new GClass('Psc\Code\Generate\GClass');
    $this->assertEquals('GClass',$gClass->getClassName());
    $this->assertEquals('\Psc\Code\Generate',$gClass->getNamespace());
    $this->assertEquals('\Psc\Code\Generate\GClass',$gClass->getName());
    
    /* test final + abstract zeuch */
    $this->assertFalse($gClass->isFinal());
    $this->assertFalse($gClass->isAbstract());
    
    $gClass->setFinal(TRUE);
    $this->assertTrue($gClass->isFinal());

    $gClass->setAbstract(TRUE);
    $this->assertTrue($gClass->isAbstract());

    $gClass->setModifier(GClass::MODIFIER_ABSTRACT, FALSE);
    $gClass->setModifier(GClass::MODIFIER_FINAL, FALSE);
    $this->assertFalse($gClass->isAbstract());
    $this->assertFalse($gClass->isFinal());
    
    /* testClass (denn da wissen wir die line-nummern besser und die ist auch abstract */
    $testClass = new GClass(new ReflectionClass(__NAMESPACE__.'\\TestClass'));
    $this->assertTrue($testClass->isAbstract());
    $this->assertFalse($testClass->isFinal());
    $this->assertEquals(self::$startLine, $testClass->getStartLine());
    $this->assertEquals(self::$endLine, $testClass->getEndLine());
    
    $testHint = new GClass('SomeClassForAHint');
    $this->assertEquals('class SomeClassForAHint {'.$cr.'}', $testHint->php(), sprintf("output: '%s'", $testHint->php()));
    
    
    //file_put_contents('D:\fixture.txt', self::$testClassCode);
    //file_put_contents('D:\compiled.txt',$testClass->php());
    $this->assertEquals(self::$testClassCode, $testClass->php(),'Code für Klasse ist nicht identisch');
    //identisch bis auf whitespaces! (das ist irgendwie ein bissl variabel, aber okay
    // geiler wäre halt assertEqualsCode, hmmm das ginge sogar mit token_get_all und so?
  }
  
  
  public function testProperty() {
    $class = new ReflectionClass(__NAMESPACE__.'\\TestClass');
    $gClass = GClass::reflectorFactory($class);
    
    $prop1 = GProperty::reflectorFactory($class->getProperty('prop1'),$gClass);
    $this->assertEquals(TRUE, $prop1->hasDefaultValue(), 'hasDefaultValue');
    $this->assertEquals('prop1',$prop1->getName());
    $this->assertEquals('banane',$prop1->getDefaultValue());
    $this->assertEquals(TRUE,$prop1->isProtected());
    $this->assertEquals("protected \$prop1 = 'banane'",$prop1->php());
    
    $prop2 = GProperty::reflectorFactory($class->getProperty('prop2'),$gClass);
    //$this->assertEquals(FALSE, $prop2->hasDefaultValue(), 'hasDefaultValue'); // php bug? hier gibt reflection TRUE als isDefault aus
    $this->assertEquals('prop2',$prop2->getName());
    $this->assertEquals(FALSE,$prop2->isProtected());
    $this->assertEquals(TRUE,$prop2->isPublic());
    $this->assertEquals(TRUE,$prop2->isStatic());
    $this->assertEquals("public static \$prop2",$prop2->php());
    
    
  }
  
  /**
   * @depends testClass
   */
  public function testClassWriter() {
    $writer = new ClassWriter();
    $class = GClass::factory(__NAMESPACE__.'\\TestClass');
    $class->setNamespace('Psc');
    $class->setParentClass(new GClass('\Psc\Object')); // dsa fügen wir unten mit str_replace hinzu
    $writer->setClass($class);
    $writer->addImport(new GClass('Special\classn\In\nspace\Banane'));
    
    $compiledFile = new File('TestClass.compiled.php',PSC::get(PSC::PATH_TESTDATA));
    if ($compiledFile->exists()) {
      $compiledFile->delete();
    }
    $this->assertFalse($compiledFile->exists());
    
    $writer->write($compiledFile);
    
    $contents = $compiledFile->getContents();
$code =
'<?php

namespace Psc;

use Psc\Code\Generate\SomeClassForAHint,
    stdClass,
    Special\classn\In\nspace\Banane;

'.self::$testClassCode.'
?>';
    
    /* extends einfügen */
    $code = str_replace('abstract class TestClass {','abstract class TestClass extends Object {',$code);
    
    //file_put_contents('D:\fixture.txt', $code);
    //file_put_contents('D:\compiled.txt',$contents);
    $this->assertEquals($code, $contents);
    
    $writer->syntaxCheck($compiledFile);
    
    $cought = FALSE;
    try {
      $writer = new ClassWriter();
      $writer->setClass($class);
      $writer->write($compiledFile);
    } catch (\Psc\Code\Generate\ClassWritingException $e) {
      // class exists
      if ($e->getCode() == \Psc\Code\Generate\ClassWritingException::OVERWRITE_NOT_SET)
        $cought = TRUE;
    }
    
    if (!$cought) {
      $this->fail('Klasse darf nicht Datei überschreiben, sondern muss eine Exception schmeissen');
    }
  }
}



abstract class TestClass {
  
  protected $prop1 = 'banane';
  
  public static $prop2;
  
  public function comboBox($label, $name, $selected = NULL, $itemType = NULL, Array $commonItemData = array()) {
    // does not matter
    
    $oderDoch = true;
  }
  
  public static function factory(SomeClassForAHint $dunno) {
  }
  
  abstract public function banane();
  
  public function method2($num, Array $p1, stdClass $std = NULL, $bun = array()) {
    $bimbam = 'pling';
    
    return 'schnurpsel';
  }
}

class SomeClassForAHint {
}
?>