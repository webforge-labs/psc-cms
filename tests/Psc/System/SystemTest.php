<?php

namespace Psc\System;

use \Psc\System\System;

/**
 * @group class:Psc\System\System
 */
class SystemTest extends \Psc\Code\Test\Base {
  
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
    System::which('banananenbaumdiesenbefehlkannesjawohlaufkeinenfallaufirgendeinemsystemgeben',System::REQUIRED);
  }

  public function testWhichNoException() {
    try {
      System::which('banananenbaumdiesenbefehlkannesjawohlaufkeinenfallaufirgendeinemsystemgeben');
    } catch (PscException $e) {
       $this->fail('Hier darf keine Exception geworfen werden');
    }
  }  
}
?>