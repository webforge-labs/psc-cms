<?php

namespace Psc\System;

use Psc\System\ExecutableFinder;
use Psc\CMS\Configuration;

/**
 * @group class:Psc\System\ExecutableFinder
 */
class ExecutableFinderTest extends \Psc\Code\Test\Base {
  
  public function testBlankConstruct() {
    $finder = new ExecutableFinder();
  }

  public function testFind() {
    $exRarBin = (string) $this->getFile('fakeExecutableRar');
    
    $config = new Configuration();
    $config->set(array('executables','rar'), $exRarBin);
    $config->set(array('executables','wrong'), '/this/path/does/notexists/rar');
    
    $finder = new ExecutableFinder($config);
    $rarBin = $finder->getExecutable('rar');
    
    $this->assertInstanceOf('Webforge\Common\System\File',$rarBin);
    $this->assertEquals($exRarBin, (string) $rarBin);
    
    $this->assertException('Psc\System\NoExecutableFoundException', function () use ($finder) {
      $finder->getExecutable('blubb');
    });

    $this->assertException('Psc\System\NoExecutableFoundException', function () use ($finder) {
      $finder->getExecutable('wrong');
    });
  }
}
?>