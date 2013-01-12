<?php

namespace Psc\Code\Generate;

use Psc\Test;
use Psc\Object;
use Webforge\Common\System\Dir;
use Webforge\Common\System\File;
use Psc\Code\Code;
use Psc\PSC;
use Psc\CMS\Configuration;

/**
 * Wir erstellen eine Test-Klasse für die angegebene Klasse
 * 
 * die \Psc\Code\Test\Base ableitet
 * Das Verzeichnis ist der Namespace ohne den ersten Teil des Namespaces in "tests"
 * Der Testklasse wird <className>Test genannt
 */
class TestCreater extends \Psc\System\LoggerObject {
  
  const OVERWRITE = '__overwrite';
  
  /**
   * Die Klasse für die der Test erstellt werden soll
   * 
   * @var GClass
   */
  protected $class;
  
  /**
   * @var Dir
   */
  protected $dir;
  
  /**
   * @var GClass
   */
  protected $testClass;
  
  /**
   * @var mixed
   */
  protected $createCallback;
  
  public function __construct(GClass $class, Dir $testsDir = NULL) {
    $this->setLogger(new \Psc\System\BufferLogger());
    $this->class = $class;
    
    $this->dir = $testsDir ?: PSC::getProject()->getTestsPath();
    $this->testClass = new GClass('\Psc\Code\Test\Base');
  }
  
  public function create($overwrite = false) {
    try {
      $this->class->elevateClass(); // @todo das mal konfigurieren und fixen
    } catch (\Exception $e) {
      $this->log('Klasse für den Test kann nicht elevated werden: '.$e->getMessage());
    } catch (\Psc\Code\Generate\ReflectionException $e) {
      $this->log('Klasse für den Test kann nicht elevated werden: '.$e->getMessage());
    }
    
    $class = new GClass();
    $class->setName($this->class->getClassName().'Test');
    $class->setNamespace($this->class->getNamespace());
    $class->setParentClass($this->testClass);
    
    $writer = new ClassWriter();
    $writer->setClass($class);
    
    $class = $this->createStub($class);
    $file = $this->getTestFile();
    
    if (isset($this->createCallback)) {
      $class = call_user_func($this->createCallback, $class, $this, $writer, $file);
    }
    
    $writer->addImport($this->class); // not really necessary (weil ist ja derselbe NS, aber who knows)
    $file->getDirectory()->create();
    
    $writer->write($file,array(),$overwrite);
    return $file;
  }
  
  public function createStub(GClass $class) {
    $setupCode  = "\$this->chainClass = '%s';\n";
    $setupCode .= "parent::setUp();\n";
    if ($this->class->isAbstract()) {
      $setupCode .= '//$this->%s = $this->getMockForAbstractClass($this->chainClass);'."\n";
    } else {
      $setupCode .= "//\$this->%s = new %s();\n";
    }
    
    $docBlock = $class->createDocBlock();
    $docBlock->addSimpleAnnotation('group class:'.$this->class->getFQN());
    
    $class->addMethod(new GMethod('setUp', array(),
                                  sprintf($setupCode,
                                          $this->class->getFQN(), lcfirst($this->class->getClassName()), $this->class->getClassName())));
    
    
    $class->addMethod(new GMethod('testAcceptance',array(),'$this->markTestIncomplete(\'Stub vom Test-Creater\');'));
    
    //$class->addMethod(new GMethod('create'.$this->class->getClassName(), array(),
                                  //sprintf("return new %s();",$this->class->getClassName())), GMethod::MODIFIER_PROTECTED);
    
    $class->createProperty(lcfirst($this->class->getClassName()), GProperty::MODIFIER_PROTECTED);
    return $class;
  }
  
  /**
   * @return Webforge\Common\System\File
   */
  public function getTestFile() {
    /* zuerst setzen wir den ns in einen relativen pfad um */
    $file = Code::mapClassToFile($this->class->getName(), NULL);
    
    /* entfernt das erste und zweite verzeichnis (das erste ist der .)*/
    $file->getDirectory()->slice(2); 
    
    /* wir fügen den rest des relativen Verzeichnisses an das tests-Verzeichnis an */
    $dir = clone $this->dir;
    $dir->append($file->getDirectory());
    $file = new File($dir, $this->class->getClassName().'Test');
    $file->setExtension('.php');
    
    return $file;
  }
  
  /**
   * erhält 3 parameter: $test GClass, $classUnderTest GClass, $lassWriter
   * @param mixed $createCallback
   */
  public function setCreateCallback($createCallback) {
    $this->createCallback = $createCallback;
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function getCreateCallback() {
    return $this->createCallback;
  }
  
  public function getTestsDir() {
    return $this->dir;
  }
  
  public function getClass() {
    return $this->class;
  }
}
?>