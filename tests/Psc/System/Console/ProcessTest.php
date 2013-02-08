<?php

namespace Psc\System\Console;

/**
 * @group class:Psc\System\Console\Process
 */
class ProcessTest extends \Psc\Code\Test\Base {
  
  protected $process;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Console\Process';
    parent::setUp();
    
    $this->echoBat = \Psc\PSC::getProject()->getBin()->getFile('echo.bat');
    
    if (substr(PHP_OS, 0, 3) == 'WIN') {
      $this->binPath = 'C:\Program Files\\';
    } else {
      $this->binPath = '/usr/local/bin/';
    }
    
    $this->win = substr(PHP_OS, 0, 3) == 'WIN';
  }
  
  /**
   * @dataProvider provideShellEscaping
   */
  public function testProcessBuilderShellEscaping(Array $expectedArguments, Array $arguments, Array $options = array()) {
    if (substr(PHP_OS, 0, 3) == 'WIN') {
      $process = Process::build($this->echoBat, $arguments, $options)
                    ->setEnv('defined','this is an defined env value')
                    ->end();
    
      $this->assertEquals(0, $process->run(), ' process did not run correctly '.$process->getCommandLine());

      $this->assertEquals($expectedArguments, \Psc\JS\JSONConverter::create()->parse($process->getOutput()));
    } else {
      $this->markTestSkipped('runs only on windows, yet');
    }
  }
  
  public static function provideShellEscaping() {
    $tests = array();
    
    $tests[] = Array(
      array('myargument'), array('myargument'),
    );
    
    $tests[] = Array(
      array('my"argument'), array('my"argument')
    );
    
    $tests[] = Array(
      array("my'argument"), array("my'argument")
    );

    $tests[] = Array(
      array('format="%h%d%Y"'), array('format="%h%d%Y"')
    );

    $tests[] = Array(
      array('%h%d%Y'), array('%h%d%Y')
    );

    $tests[] = Array(
      array('this is an defined env value'), array('%defined%')
    );

    // you have not a chance to escape it
    // this test is NOT used yet, i still didnt find out how to escape % sign here to make it literal
    $tests[] = Array(
      array('%this is an defined env value%'), array('%%defined%%')
    );
    
    
    return $tests;
  }
  
  
  public function testBuilderIsSensitivToEscapeForChangesWhenArgumentsAddedThroughConstructor() {
    $this->setExpectedException('RuntimeException');
    
    Process::build(new \Webforge\Common\System\File($this->binPath.'mybin'), array('myarg1','myarg2'), array(), Process::WINDOWS)
      ->escapeFor(Process::UNIX);
    
  }

  public function testBuilderIsSensitivToEscapeForChangesWhenAddedThroughAddArgument() {
    $this->setExpectedException('RuntimeException');
    
    Process::build(new \Webforge\Common\System\File($this->binPath.'mybin'))
      ->escapeFor(Process::UNIX)
      ->addArgument('arg1')
      ->escapeFor(Process::WINDOWS)
    ;
  }
  
  public function testCreateReturnsBuilderWithParamsAndOptions() {
    $process = Process::build(new \Webforge\Common\System\File($this->binPath.'mybin'),
                               array('argument1', 'escaped\argument2'),
                               array('flag1'=>'value',
                                     'flag2',
                                     'flag3'=>'escaped"value'
                                     )
                               )
                  ->setInput('piped in')
                  ->end();
      ;
      
    $this->assertInstanceOf('Psc\System\Console\Process', $process);
    
    $this->assertEquals('piped in', $process->getStdin());
    
    if ($this->win) {
      $cmdLine = <<<'WINDOWS'
"C:\Program Files\mybin" "argument1" "escaped\argument2" --flag1="value" --flag2 --flag3="escaped\"value"
WINDOWS;
    } else {
      $cmdLine = <<<'UNIX'
/usr/local/bin/mybin 'argument1' 'escaped\argument2' --flag1='value' --flag2 --flag3='escaped"value'
UNIX;
    }

    $this->assertEquals($cmdLine, $process->getCommandLine());
  }
  
  public function testBuilderConstructsShortOptionsOnlyWithOneMinus() {
    $process = Process::build(new \Webforge\Common\System\File($this->binPath.'mybin'))
                  ->addOption('s', 'short-argument')
                  ->end();
    
    $this->assertContains('-s', $process->getCommandLine());
    $this->assertNotContains('--s', $process->getCommandLine());
  }
}
?>