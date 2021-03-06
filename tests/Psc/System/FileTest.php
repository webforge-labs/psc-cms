<?php

namespace Webforge\Common\System\File;

use \Webforge\Common\System\File;
use \Webforge\Common\System\Dir;
use \Psc\PSC;

/**
 * @group class:Webforge\Common\System\File
 */
class FileTest extends \Psc\Code\Test\Base {
  
  protected static $absPathPrefix;
  
  public static function setUpBeforeClass() {
    self::$absPathPrefix = \Psc\PSC::getEnvironment()->isWindows() ? 'D:\\' : '/';
  }
  
  // erstellt einen Pfad mit trailing slash
  public static function path() {
    return implode(DIRECTORY_SEPARATOR, func_get_args()).DIRECTORY_SEPARATOR;
  }
  
  public static function absPath() {
    return self::$absPathPrefix.implode(DIRECTORY_SEPARATOR, func_get_args()).DIRECTORY_SEPARATOR;
  }
  
  public function testConstructor() {
    $fileString = self::absPath('www', 'test', 'base', 'ka', 'auch').'banane.php';
    
    $dir = new Dir(self::absPath('www', 'test', 'base', 'ka', 'auch'));
    $filename = 'banane.php';
    
    $file = new File($dir, $filename);
    $this->assertEquals($fileString, (string) $file);
    
    $file = new File($fileString);
    $this->assertEquals($fileString, (string) $file);
    
    $file = new File($filename, $dir);
    $this->assertEquals($fileString, (string) $file);
  }
  
  public function testWrappedConstructor() {
    $fileString = 'phar://'.($pf = \Psc\PSC::getEnvironment()->isWindows() ? 'D:/' : '/').'does/not/matter/my.phar.gz/i/am/wrapped/class.php';
    
    $file = new File($fileString);
    $this->assertEquals('php',$file->getExtension());
    $this->assertEquals('class.php',$file->getName());
    $this->assertEquals('phar://'.$pf.'does/not/matter/my.phar.gz/i/am/wrapped/', (string) $file->getDirectory());
    $this->assertEquals($fileString, (string) $file);
  }
  
  public function testReadableinPhar() {
    $phar = $this->getFile('test.phar.gz');
    $wrapped = 'phar://'.str_replace(DIRECTORY_SEPARATOR, '/', (string) $phar).'/Imagine/Exception/Exception.php';
    
    $file = new File($wrapped);
    $this->assertTrue($file->isReadable());
    $this->assertTrue($file->exists());
  }
  
  public function testAppendName() {
    $path = self::absPath('Filme', 'Serien', 'The Big Bang Theory', 'Season 5');
    
    $file = new File($path.'The.Big.Bang.Theory.S05E07.en.IMMERSE.srt');
    $file->setName($file->getName(File::WITHOUT_EXTENSION).'-en.srt');
    
    $this->assertEquals($path.'The.Big.Bang.Theory.S05E07.en.IMMERSE-en.srt',(string) $file);
  }
  
  /**
   * @expectedException \BadMethodCallException
   */
  public function testConstructorException1() {
    $file = new File('keindir','keinfilename');
  }

  /**
   * @expectedException \BadMethodCallException
   */
  public function testConstructorException2() {
    $file = new File(new File('/tmp/src'));
  }
  
  /**
   * @dataProvider provideGetURL
   */
  public function testGetURL($expectedURL, $fileString, $dirString = NULL) {  
    $file = new File($fileString);
    $dir = isset($dirString) ? new Dir($dirString) : NULL;
    
    $this->assertEquals($expectedURL, $file->getURL($dir));
  }
  
  public static function provideGetURL() {
    $tests = array();
    $test = function ($file, $dir, $url) use (&$tests) {
      $tests[] = array($url, $file, $dir);
    };
    
    $test(self::absPath('www', 'test', 'base', 'ka', 'auch').'banane.php',
          self::absPath('www', 'test', 'base', 'ka'),
          '/auch/banane.php');
    $test(self::absPath('www', 'psc-cms', 'Umsetzung', 'base', 'src', 'tpl').'throwsException.html',
          self::absPath('www', 'psc-cms', 'Umsetzung', 'base', 'src', 'tpl'),
          '/throwsException.html'
         );
    
    return $tests;
  }
  
  public function testGetURL_noSubdir() {
    $fileString = self::absPath('www', 'test', 'base', 'ka', 'auch').'banane.php';
    $file = new File($fileString);
  }

  public function testStaticCreateFromURL() {
    $dir = new Dir($path = self::absPath('www', 'ePaper42', 'Umsetzung', 'base', 'files', 'testdata', 'fixtures', 'ResourceManagerTest', 'xml'));
    $url = "/in2days/2011_newyork/main.xml";
    
    $this->assertEquals($path.'in2days'.DIRECTORY_SEPARATOR.'2011_newyork'.DIRECTORY_SEPARATOR.'main.xml',
                        (string) File::createFromURL($url, $dir));
    $this->assertEquals(self::path('.', 'in2days', '2011_newyork'). 'main.xml', (string) File::createFromURL($url));
  }
    
  public function testGetFromURL_relativeFile() {
    // wird als Datei interpretiert die in in2days/ liegt !
    $url = "/in2days/2011_newyork";
    $this->assertEquals('.'.DIRECTORY_SEPARATOR.'in2days'.DIRECTORY_SEPARATOR.'2011_newyork', (string) File::createFromURL($url));
  }
  
  public function testSha1Hashing() {
    $content = 'sldfjsldfj';
    $otherContent = 's00000000';
    $file = File::createTemporary();
    $file->writeContents($content);
    $this->assertEquals(sha1($content), $file->getSha1());
    
    // test caching
    $file->writeContents($otherContent);
    //$this->assertNotEquals(sha1($content), $file->getSha1());
    $this->assertEquals(sha1($otherContent), $file->getSha1());
  }
}