<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Psc\Code\Generate\GClass;
use Psc\Code\Generate\GParameter;
use Psc\System\File;

/**
 * @group class:Psc\System\Console\GenericCompileCommand
 */
class GenericCompileCommandTest extends \Psc\Code\Test\Base {
  
  protected $command;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Console\GenericCompileCommand';
    parent::setUp();
  }
  
  public function testParametersJSONGoToConstructor() {
    $out = $this->newFile('outfile.php');
    
    $compilerMock = $this->getMock('Psc\Code\Compile\Compiler', array('compile','addExtension'), array(), '', FALSE);
    
    $extension = NULL;
    $compilerMock->expects($this->once())->method('addExtension')
      ->will($this->returnCallback(function ($givenExtension) use (&$extension, $compilerMock) {
          $extension = $givenExtension;
          return $compilerMock;
        }));
    $compilerMock->expects($this->once())->method('compile');
    
    // wir injecten unsere gefakte GClass in den command mit self-stunning
    $command = $this->getMock($this->chainClass, array('getCompiler'));
    $command->expects($this->once())->method('getCompiler')
      ->will($this->returnValue(
        $compilerMock
      ));
    
    // wir führen den command aus
    $tester = new CommandTester($command);
    $tester->execute(Array(
      'file' => (string) $this->getFile('class.NormalClass.php'),
      'class' => 'inFile',
      'name' => __NAMESPACE__.'\MockedExtension',
      'parametersJSON' => json_encode(
        (object) $params = array(
          'param2'=>'value2',
          'param3'=>array('value3'),
          'param1'=>1,
          
          'property1'=>'v1',
          'property2'=>(object) array(
            'some'=>'moreComplext',
            array('value','in','it')
          )
        )
      )
    ));
    
    $this->assertEquals(array($params['param1'], $params['param2'], $params['param3']), $extension->constructor);
    $this->assertEquals($params['property1'], $extension->property1);
    $this->assertEquals($params['property2'], $extension->property2);
  }
}

class MockedExtension extends \Psc\Code\Compile\Extension {
  
  public $constructor;
  
  public $property1;
  public $property2;
  
  public function __construct($param1, $param2, Array $param3 = array()) {
    $this->constructor = func_get_args();
  }
  
  public function compile(GClass $gClass, $flags = 0x000000) {
  }
  
  /**
   * @param string $property1
   * @chainable
   */
  public function setProperty1($property1) {
    $this->property1 = $property1;
    return $this;
  }

  /**
   * @return string
   */
  public function getProperty1() {
    return $this->property1;
  }

  /**
   * @param string $property2
   * @chainable
   */
  public function setProperty2($property2) {
    $this->property2 = $property2;
    return $this;
  }

  /**
   * @return string
   */
  public function getProperty2() {
    return $this->property2;
  }
}
?>