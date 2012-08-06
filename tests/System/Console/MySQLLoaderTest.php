<?php

namespace Psc\System\Console;

use Psc\System\Console\MySQLLoader;
use Psc\CMS\Configuration;
use Psc\System\FIle;

/**
 * @group class:Psc\System\Console\MySQLLoader
 */
class MySQLLoaderTest extends \Psc\Code\Test\Base {

  public function testCommandGeneration() {
    
    $config = new Configuration(array('user'=>'root',
                                      'password'=>'ichbingeheim',
                                      'host'=>'localhorst'
                                      ));
    
    $mysql = \Psc\System\System::which('mysql');
    $loader = new MySQLLoader('importdatenbank',$config);
    
    $cmd = $loader->loadFromFile(new File('test.sql'), MySQLLoader::JUST_RETURN);
    
    $this->assertEquals($mysql.' --host=localhorst --user=root --password=ichbingeheim importdatenbank < .'.DIRECTORY_SEPARATOR.'test.sql', $cmd);
  }
}
?>