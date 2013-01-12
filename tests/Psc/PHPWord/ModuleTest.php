<?php

namespace Psc\PHPWord;

use Psc\PSC;

/**
 * @group class:Psc\PHPWord\Module
 */
class ModuleTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\PHPWord\Module';
    parent::setUp();
  }
  
  public function testConstruct() {
    $module = PSC::getProject()->getModule('PHPWord')->bootstrap();
    
    $this->assertGreaterThan(0, count($files = $module->getAdditionalPharFiles()), 'mehrere zusätzliche Files erwartet');
    foreach ($files as $url => $file) {
      $this->assertStringStartsWith('/PHPWord', $url, 'Jede Zusätzliche File muss in /PHPWord');
      $this->assertFileExists((string) $file);
      //print $url.'  =>  '.$file."\n";
    }
  }
  
  public function createModule() {
    return new Module();
  }
}
?>