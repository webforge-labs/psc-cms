<?php

use \Psc\System\File;
use \Psc\System\Dir;
use \Psc\PSC;

class FileTest extends \Psc\Code\Test\Base {
  
  public function testConstructor() {
    
    $fileString = 'D:\www\test\base\ka\auch\banane.php';
    
    $dir = new Dir('D:\www\test\base\ka\auch\\');
    $filename = 'banane.php';
    
    $file = new File($dir, $filename);
    $this->assertEquals($fileString, (string) $file);
    
    $file = new File($fileString);
    $this->assertEquals($fileString, (string) $file);
    
    $file = new File($filename, $dir);
    $this->assertEquals($fileString, (string) $file);
  }
  
  public function testWrappedConstructor() {
    $fileString = 'phar://path/does/not/matter/my.phar.gz/i/am/wrapped/class.php';
    
    $file = new File($fileString);
    $this->assertEquals('php',$file->getExtension());
    $this->assertEquals('class.php',$file->getName());
    $this->assertEquals('phar://path/does/not/matter/my.phar.gz/i/am/wrapped/', (string) $file->getDirectory());
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
    $file = new File('D:\Filme\Serien\The Big Bang Theory\Season 5\The.Big.Bang.Theory.S05E07.en.IMMERSE.srt');
    $file->setName($file->getName(File::WITHOUT_EXTENSION).'-en.srt');
    $this->assertEquals('D:\Filme\Serien\The Big Bang Theory\Season 5\The.Big.Bang.Theory.S05E07.en.IMMERSE-en.srt',(string) $file);
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
    
    $test('D:\www\test\base\ka\auch\banane.php', 'D:\www\test\base\ka\\',
          '/auch/banane.php');
    $test('D:\www\psc-cms\Umsetzung\base\src\tpl\throwsException.html', 'D:\www\psc-cms\Umsetzung\base\src\tpl\\',
          '/throwsException.html'
          );
    
    return $tests;
  }
  
  public function testGetURL_noSubdir() {
    $fileString = 'D:\www\test\base\ka\auch\banane.php';
    $file = new File($fileString);
  }

  public function testGetCreateFromURL() {
    $dir = new Dir('D:\www\ePaper42\Umsetzung\base\files\testdata\fixtures\ResourceManagerTest\xml\\');
    $url = "/in2days/2011_newyork/main.xml";
    
    $this->assertEquals('D:\www\ePaper42\Umsetzung\base\files\testdata\fixtures\ResourceManagerTest\xml\in2days\2011_newyork\main.xml', (string) File::createFromURL($url, new Dir($dir)));
    $this->assertEquals('.\in2days\2011_newyork\main.xml', (string) File::createFromURL($url));
  }
    
  public function testGetFromURL_relativeFile() {
    // wird als Datei interpretiert die in in2days/ liegt !
    $url = "/in2days/2011_newyork";
    $this->assertEquals('.\in2days\2011_newyork', (string) File::createFromURL($url));
  }
}