<?php

namespace Psc\System;

use \Psc\System\System;

/**
 * @group class:Psc\System\System
 */
class SystemTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->notAvaibleCmd = 'banananenbaumdiesenbefehlkannesjawohlaufkeinenfallaufirgendeinemsystemgeben';
  }
  
  public function testWhich() {
    
    /* na wie das mal hier portabel sein soll?
      some other ideas?
    */
    return TRUE;
    
    foreach (Array('mysql' => '"C:\Program Files\MySQL\MySQL Server 5.5\bin\mysql.exe"',
                   'cmd' => 'c:\WINDOWS\system32\cmd.exe',
                   'ssh' => 'd:\stuff\cygwin\root\bin\ssh.exe'
                   ) as $search => $result) {
      
      $this->assertEquals($result, System::which($search));
    }
    
    $this->assertEquals('c:\Programme\MySQL\MySQL Server 5.5\bin\mysql.exe', System::which('mysql',System::DONTQUOTE));
    
    foreach (Array('mysql' => '/cygdrive/c/Programme/MySQL/MySQL Server 5.5/bin/mysql',
                   'cmd' => '/cygdrive/c/WINDOWS/system32/cmd',
                   'ssh' => '/usr/bin/ssh',
                   ) as $search => $result) {
      
      $this->assertEquals($result, System::which($search, System::FORCE_UNIX));
    }
    

  }
  
  /**
   * @expectedException \Psc\Exception
   */
  public function testWhichException() {
    System::which($this->notAvaibleCmd,System::REQUIRED);
  }

  public function testWhichNoException() {
    $this->assertEquals($this->notAvaibleCmd, System::which($this->notAvaibleCmd));
  }

  /**
   * @dataProvider provideForceUnix
   */
  public function testForceUnix($path, $expectedPath) {
    $dir = new Dir($path);
    $unixDir = System::forceUnixPath($dir);

    $this->assertInstanceOf('Webforge\Common\System\Dir', $unixDir);

    $this->assertEquals(
      $expectedPath,
      (string) $unixDir
    );
  }
  
  public static function provideForceUnix() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test('D:\www\psc-cms-js\git\\', '/cygdrive/D/www/psc-cms-js/git/');
    $test('C:\windows\\', '/cygdrive/C/windows/');

    $test('/var/local/www/', '/var/local/www');
    $test('local/www/', 'local/www');

    $test('.\what\todo\with\this', './what/todo/with/this');
  
    return $tests;
  }
}
