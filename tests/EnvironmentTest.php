<?php

namespace Psc;

use \Psc\PSC;

/**
 * @group class:Psc\Environment
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase {
  
  protected $saveIncludePath;
  
  public function setUp() {
    $this->saveIncludePath = get_include_path();
  }

  public function testPHPSettings() {
    $this->assertAndTrySetINI('mbstring.internal_encoding', 'UTF-8');
    $dir = new \Psc\System\Dir(getenv('PSC_CMS'));
    $this->assertTrue($dir->exists(), $dir.' existiert nicht');
    $this->assertFileExists((string) ($file = $dir->getFile('bootstrap.php')));
  }
  
  protected function assertINI($iniName, $iniValue) {
    $this->assertEquals($iniValue, ini_get($iniName), 'php.ini value: '.$iniName.' muss korrekt gesetzt sein');
  }
  
  protected function assertAndTrySetINI($iniName, $iniValue) {
    if (ini_get($iniName) != $iniValue) {
      ini_set($iniName, $iniValue);
    }
    
    $this->assertINI($iniName, $iniValue);
  }
  
  
  public function testAdd() {
    /* jetzt wo er leer ist, können wir ja hinzufügen */
    if (PSC::isTravis()) {
      $this->markTestSkipped('include path kann in travis nicht geändert werden');
    }
    
    $src = (string) PSC::get(PSC::PATH_SRC)->append('/nichtiminc/');
    $htdocs = (string) PSC::get(PSC::PATH_HTDOCS);
    $temp = 'D:\temp\sessions';
    $env = PSC::getEnvironment();
    
    $this->assertFalse($env->hasIncludePath($src),'hasIncludePath');
    $this->assertInstanceOf('\Psc\Environment',$env->addIncludePath($src,'append'));
    $this->assertTrue($env->hasIncludePath($src),'hasIncludePath');
    $this->assertEquals(get_include_path(), $this->saveIncludePath.PATH_SEPARATOR.$src);

    $this->assertFalse($env->hasIncludePath($htdocs),'hasIncludePath');
    $this->assertInstanceOf('\Psc\Environment',$env->addIncludePath($htdocs,'append'));
    $this->assertTrue($env->hasIncludePath($src),'hasIncludePath');
    $this->assertEquals(get_include_path(), $this->saveIncludePath.PATH_SEPARATOR.$src.PATH_SEPARATOR.$htdocs);

    $this->assertFalse($env->hasIncludePath($temp),'hasIncludePath');
    $this->assertInstanceOf('\Psc\Environment',$env->addIncludePath($temp)); // prepend ist default
    $this->assertTrue($env->hasIncludePath($temp),'hasIncludePath');
    $this->assertEquals(get_include_path(), $temp.PATH_SEPARATOR.$this->saveIncludePath.PATH_SEPARATOR.$src.PATH_SEPARATOR.$htdocs);
  }
  
  public function testToSubstr() {
    $env = PSC::getEnvironment();
    $env->addIncludePath('/var/share/PEAR/');
    
    $evil = '/var/share/';
    $this->assertFalse($env->hasIncludePath($evil),'hasIncludePath');
    $this->assertInstanceOf('\Psc\Environment',$env->addIncludePath($evil),'append'); 
    $this->assertTrue($env->hasIncludePath($evil),'hasIncludePath');
    
  }
  
  public function tearDown() {
    // nicht restore_include_path(), denn das nimmt auch den automatisch hinzugefügten include path für die tests hinzu
    set_include_path($this->saveIncludePath);
  }
  
}