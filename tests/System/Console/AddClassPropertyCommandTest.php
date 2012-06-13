<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Psc\Code\Generate\GClass;
use Psc\System\File;

/**
 * @group class:Psc\System\Console\AddClassPropertyCommand
 */
class AddClassPropertyCommandTest extends \Psc\Code\Test\Base {
  
  protected $addClassPropertyCommand;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Console\AddClassPropertyCommand';
    parent::setUp();
  }
  
  public function testInterfaceAndNotOwnPropertiesAcceptance() {
    $out = $this->newFile('class.CompiledPropertyAddTestClass.php');

    $this->runCommand(Array(
      'file' => (string) $this->getFile('class.PropertyAddTestClass.php'),
      'class' => 'inFile',
      'name' => 'entityMeta',
      'type' => 'Psc\CMS\EntityMeta',
      '--out' => (string) $out
    ));
    
    $this->test->gClass($gClass = $this->getCClass($out,'PropertyAddTestClass'))
      ->hasMethod('setEntityMeta')
      ->hasMethod('getEntityMeta')
      ->hasProperty('entityMeta')
      ->hasNotOwnProperty('baseProperty')
      ->hasNotInterface('PropertyAddInterfaceTestClass')
    ;
  }
  
  public function testNormalClassAdding() {
    $out = $this->newFile('class.CompiledNormalClass.php');
    
    $this->runCommand(Array(
      'file' => (string) $this->getFile('class.NormalClass.php'),
      'class' => 'Psc\System\Console\NormalClass',
      'name' => 'identifier',
      'type' => 'PositiveInteger',
      '--out' => (string) $out
    ));
    
    $constructor = $this->test->gClass($gClass = $this->getCClass($out))
      ->hasProperty('identifier')
      ->hasMethod('getIdentifier')
      ->hasMethod('setIdentifier', array('identifier'))
      ->hasMethod('__construct', array('name','identifier'))
      ->get();
    
    $this->assertContains('$this->setIdentifier($identifier);',$constructor->php());
    
    $property = $gClass->getProperty('identifier');
    $db = $property->getDocBlock();
    $this->assertNotNull($db);
  }

  public function testTypeCanBeAFQNClassName() {
    $out = $this->newFile('class.CompiledNormalClass2.php');
    
    $this->runCommand(Array(
      'file' => (string) $this->getFile('class.NormalClass2.php'),
      'class' => 'Psc\System\Console\NormalClass2',
      'name' => 'entityMeta',
      'constructor' => 'prepend',
      'type' => 'Psc\CMS\EntityMeta',
      '--out' => (string) $out
    ));
    
    $setEntityMeta = $this->test->gClass($gClass = $this->getCClass($out,'NormalClass2'))
      ->hasProperty('entityMeta')
      ->hasMethod('getEntityMeta')
      ->hasMethod('__construct', array('entityMeta','name'))
      ->hasMethod('setEntityMeta', array('entityMeta'))
      ->get();
    
    $entityMetaParam = $setEntityMeta->getParameter('entityMeta');
    $this->assertEquals('Psc\CMS\EntityMeta', $entityMetaParam->getHint()->getFQN());
  }


  protected function runCommand(Array $args) {
    $application = new Application();
    $application->add(new AddClassPropertyCommand());

    $command = $application->find('compile:property');
    $this->commandTester = new CommandTester($command);
    return $this->commandTester->execute(array_merge(array(
      'command' => $command->getName(),
    ), $args));
  }
  
  protected function getCClass(File $out, $className = 'NormalClass') {
    // das hier ist nicht wahr
    //$this->assertFalse(class_exists('Psc\System\Console\NormalClass',FALSE));
    // weil der commandTester die Klasse lädt (bzw der Command) müssen wir die $out file hier umbenennen um das Ergebnis testen zu können
    $ccName = 'Compiled'.$className;
    
    $out->writeContents(
      str_replace(
                  'class '.$className.' ',
                  'class '.$ccName.' ',
                  $out->getContents()
                 )
    );
    
    require $out;
    
    $gClass = GClass::factory('Psc\System\Console\\'.$ccName);
    return $gClass;
  }

  protected function onNotSuccessFullTest(\Exception $e)  {
    print $this->commandTester->getDisplay();
    throw $e;
  }
}
?>