<?php

namespace Psc\System\Test;

use \Psc\System\Dir;
use \Psc\System\File;
use \Psc\PSC;

/**
 * @group class:Psc\System\Dir
 */
class DirTest extends \Psc\Code\Test\Base {
  
  public function testSubDir() {
    $sub = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $parent = new Dir(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);
    
    $this->assertTrue($sub->isSubdirectoryOf($parent));
  }
  
  /**
   * @depends testSubDir
   */
  public function testMakeRelativeTo() {
    
    $graph = PSC::get(PSC::PATH_SRC)->append('psc/class/Graph//');
    $psc = PSC::get(PSC::PATH_SRC)->append('psc/');
    
    $rel = clone $graph;
    
    $this->assertEquals('.'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'Graph'.DIRECTORY_SEPARATOR,
                        (string) $rel->makeRelativeTo($psc));
    
    $eq = clone $graph;
    
    $this->assertEquals('.'.DIRECTORY_SEPARATOR,(string) $eq->makeRelativeTo($graph));
  }
  
  /**
   * @expectedException \Psc\System\Exception
   */
  public function testMakeRelativeToException() {
    $sub = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $parent = new Dir(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);
    
    $norel = clone $parent;
    $norel->makeRelativeTo($sub);
  }
  
  public function testDirectoryIsNotSubdirectoryOfSelf() {
    $dir = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $self = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    
    $this->assertFalse($dir->isSubdirectoryOf($self));
  }
  
  public function testDirGetFile() {
    $dir = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $file = __FILE__;
    $fname = basename($file);
    
    //$this->assertEquals('D:\www\psc-cms\Umsetzung\base\src\psc\readme.txt',
                        //(string) $dir->getFile('readme.txt'));

    $this->assertEquals($file, (string) $dir->getFile($fname));
    $this->assertEquals($file, (string) $dir->getFile(new File($fname)));
    $this->assertEquals($file, (string) $dir->getFile(new File(new Dir('.'.DIRECTORY_SEPARATOR),$fname)));

  /*
    das ist unexpected! ich will aber keinen test auf sowas machen..
    $this->assertEquals('D:\www\psc-cms\Umsetzung\base\readme.txt',
                        (string)  $dir->getFile('..\\..\\readme.txt'));
  */

    if (DIRECTORY_SEPARATOR === '\\') {
      $this->assertEquals(__DIR__.'\lib\docu\readme.txt',
                          (string) $dir->getFile(new File('.\lib\docu\readme.txt')));
      $this->assertEquals(__DIR__.'\lib\docu\readme.txt',
                          (string) $dir->getFile(new File(new Dir('.\lib\docu\\'),'readme.txt')));
                          
    } else {
      $this->assertEquals(__DIR__.'/lib/docu/readme.txt',
                          (string) $dir->getFile(new File('./lib/docu/readme.txt')));
      $this->assertEquals(__DIR__.'/lib/docu/readme.txt',
                          (string) $dir->getFile(new File(new Dir('./lib/docu/'),'readme.txt')));
      
    }
    

    $absoluteDir = __DIR__.DIRECTORY_SEPARATOR;
    $this->assertException('InvalidArgumentException', function () use ($dir, $absoluteDir) {
      $dir->getFile(new File(new Dir($absoluteDir),'readme.txt'));
    });
  }
  
  public function testDirgetFiles() {
    $dir = PSC::get(PSC::PATH_SRC)->append('../htdocs/js/ui-dev')->resolvePath(); // sollte dasein für jquery ui-dev test, ka
    
    if ($dir->exists()) {
      $files = $dir->getFiles('js',array('.svn'));
      $this->assertNotEmpty($files);
      
      foreach ($files as $file) {
        if (isset($dirone)) {
          $this->assertFalse($dirone === $file->getDirectory(),'Die Verzeichnisse der dateien von getFiles() müssen kopien des ursprünglichen objektes sein. keine Referenzen');
          $this->assertFalse($dirone === $dir,'Die Verzeichnisse der dateien von getFiles() müssen kopien des ursprünglichen objektes sein. keine Referenzen');
        }
        
        $dirone = $file->getDirectory();
        $this->assertFalse($file->getDirectory()->isRelative());
        
        $file->makeRelativeTo($dir);
        
        $this->assertTrue($file->isRelative());
      }
    } else {
      $this->markTestSkipped('ui dev für test nicht da');
    }
  }
  
  public function testIsRelative() {
    $graph = PSC::get(PSC::PATH_SRC)->append('psc/class/Graph//');
    $psc = PSC::get(PSC::PATH_SRC)->append('psc/');
    
    $graph->makeRelativeTo($psc);
    
    $this->assertTrue($graph->isRelative());
  }
  
  
  public function testWrappedPaths() {
    $wrappedPath = 'phar://path/does/not/matter/my.phar.gz/i/am/wrapped/';
    
    $dir = new Dir($wrappedPath);
    $this->assertEquals($wrappedPath, (string) $dir);
    
    $this->assertTrue($dir->isWrapped());
    $this->assertEquals('phar', $dir->getWrapper());
    
    $dir->setWrapper('rar');
    $this->assertEquals('rar', $dir->getWrapper());
  }
  
  public function testWrappedExtract() {
    $fileString = 'phar://path/does/not/matter/my.phar.gz/i/am/wrapped/class.php';
    
    $dir = Dir::extract($fileString);
    $this->assertEquals('phar://path/does/not/matter/my.phar.gz/i/am/wrapped/', (string) $dir);
  }
  
  public function testIsEmpty() {
    $nonEx = $this->getTestDirectory('blablabla/non/existent/');
    $this->assertTrue($nonEx->isEmpty());
    
    $temp = Dir::createTemporary();
    $this->assertTrue($temp->isEmpty());
    
    $f = $temp->getFile('blubb.txt');
    $f->writeContents('wurst');
    $this->assertFileExists((string) $f);
    $this->assertFalse($temp->isEmpty());
  }
}

?>