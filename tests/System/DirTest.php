<?php

use \Psc\System\Dir;
use \Psc\System\File;
use \Psc\PSC;

class DirTest extends \Psc\Code\Test\Base {
  
  public function testSubDir() {
    $graph = new Dir('D:\www\psc-cms\Umsetzung\base\src\psc\class\Graph\\');
    $psc = new Dir('D:\www\psc-cms\Umsetzung\base\src\psc\\');
    
    $this->assertTrue($graph->isSubdirectoryOf($psc));
  }
  
  /**
   * @depends testSubDir
   */
  public function testMakeRelativeTo() {
    
    $graph = PSC::get(PSC::PATH_SRC)->append('psc/class/Graph//');
    $psc = PSC::get(PSC::PATH_SRC)->append('psc/');
    
    $rel = clone $graph;
    
    $this->assertEquals('.\class\Graph\\',(string) $rel->makeRelativeTo($psc));
    
    $eq = clone $graph;
    
    $this->assertEquals('.'.DIRECTORY_SEPARATOR,(string) $eq->makeRelativeTo($graph));
  }
  
  /**
   * @expectedException \Psc\System\Exception
   */
  public function testMakeRelativeToException() {
    $graph = new Dir('D:\www\psc-cms\Umsetzung\base\src\psc\class\Graph\\');
    $psc = new Dir('D:\www\psc-cms\Umsetzung\base\src\psc\\');
    
    $norel = clone $psc;

    $norel->makeRelativeTo($graph);
  }
  
  public function testSubdirectoryEqualy() {
    $graph = new Dir('D:\www\psc-cms\Umsetzung\base\src\psc\class\Graph\\');
    $graph2 = new Dir('D:\www\psc-cms\Umsetzung\base\src\psc\class\Graph\\');
    
    $this->assertFalse($graph->isSubdirectoryOf($graph2));
  }
  
  public function testDirGetFile() {
    $dir = new Dir('D:\www\psc-cms\Umsetzung\base\src\psc\\');
    
    $this->assertEquals('D:\www\psc-cms\Umsetzung\base\src\psc\readme.txt',
                        (string) $dir->getFile('readme.txt'));
    $this->assertEquals('D:\www\psc-cms\Umsetzung\base\src\psc\readme.txt',
                        (string) $dir->getFile(new File('readme.txt')));
    $this->assertEquals('D:\www\psc-cms\Umsetzung\base\src\psc\readme.txt',
                        (string) $dir->getFile(new File(new Dir('.\\'),'readme.txt'))
                        );


/*
  das ist unexpected! ich will aber keinen test auf sowas machen..
  $this->assertEquals('D:\www\psc-cms\Umsetzung\base\readme.txt',
                      (string)  $dir->getFile('..\\..\\readme.txt'));
*/

    $this->assertEquals('D:\www\psc-cms\Umsetzung\base\src\psc\lib\docu\readme.txt',
                        (string) $dir->getFile(new File('.\lib\docu\readme.txt')));
    $this->assertEquals('D:\www\psc-cms\Umsetzung\base\src\psc\lib\docu\readme.txt',
                        (string) $dir->getFile(new File(new Dir('.\\lib\docu\\'),'readme.txt'))
                        );
    

    $this->assertException('InvalidArgumentException', function () use ($dir) {
        $dir->getFile(new File(new Dir('D:\www\ich\bin\absolut\\'),'readme.txt'));
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
      $this->markTestSkipped();
    }
  }
  
  public function testIsRelative() {
    $graph = PSC::get(PSC::PATH_SRC)->append('psc/class/Graph//');
    $psc = PSC::get(PSC::PATH_SRC)->append('psc/');
    
    $graph->makeRelativeTo($psc);
    
    $this->assertTrue($graph->isRelative());
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