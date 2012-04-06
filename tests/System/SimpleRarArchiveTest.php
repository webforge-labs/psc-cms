<?php

namespace Psc\System;

use Psc\System\SimpleRarArchive;
use Psc\CMS\Configuration;
use Psc\PSC;

class SimpleRarArchiveTest extends \Psc\Code\Test\Base {
  
  protected $finder;
  
  public function setUp() {
    $config = PSC::getProject()->getConfiguration();
    if ($config->get('executables.rar') === NULL) {
      // sonst macht der test hier keinen sinn
      $this->markTestSkipped('Kann Test nicht ausführen, da kein rar-Executable in der Config angegeben ist.');
    }
    $this->finder = new ExecutableFinder($config);
    
  }
  
  protected function getArchive($name) {
    $rar = $this->getFile($name);
    $archive = new SimpleRarArchive($rar, $this->finder);
    return $archive;
  }
  
  public function testExtract() {
    $archive = $this->getArchive('3files.rar');
    
    $dir = $this->getTestDirectory('extract');
    $dir->wipe();
    
    $archive->extractTo($dir);
    
    $files = $dir->getFiles();
    $files = array_map(function ($file) use ($dir) {
      $file->makeRelativeTo($dir);
      return mb_substr((string) $file,2); // schnedet ./ oder .\ ab
    },$files);
    sort($files);
    
    $this->assertEquals(array('3bet.txt',
                              'How.I.Met.Your.Mother.S07E03.Ducky.Tie.720p.HDTV.x264-IMMERSE.VO-SubCentral.srt',
                              'my'.DIRECTORY_SEPARATOR.'out.php'
                              ),
                        $files
                       );
  }
  
  public function testExtractFile() {
    $destination = $this->newFile('unpacked.srt');
    $destination->delete();
    
    $archive = $this->getArchive('3files.rar');
    
    $files = $archive->listFiles();
    sort($files);
    $srt = $files[1];
    
    $archive->extractFile($srt, $destination);
    
    $this->assertFileExists((string) $destination);
    $this->assertGreaterThan(0,$destination->getSize());
    
    // das asserted, dass der Anfang der Datei gleich ist, da hier die Credits von unrar nicht reinsollen
    $this->assertEquals($start = "1\r\n00:00:00,475 --> 00:00:04,995\r\nHey, what do you guys think of my new ducky tie?\r\nPretty cute, right?", 
    mb_substr($destination->getContents(), 0, 103),
    \Psc\String::debugEquals($start, mb_substr($destination->getContents(), 0, 103))
    );
  }

  public function testList() {
    $archive = $this->getArchive('IMMERSE.en.rar');
    $list = $archive->listFiles();
    $this->assertEquals(array('How.I.Met.Your.Mother.S07E03.Ducky.Tie.720p.HDTV.x264-IMMERSE.VO-SubCentral.srt'), $list);

    $archive = $this->getArchive('2files.rar');
    $list = $archive->listFiles();
    $this->assertEquals(array('How.I.Met.Your.Mother.S07E03.Ducky.Tie.720p.HDTV.x264-IMMERSE.VO-SubCentral.srt',
                              '3bet.txt'
                             ), $list);

    $archive = $this->getArchive('MyNewProject.rar');
    $list = $archive->listFiles();
     
    $exList = array( 
      'MyNewProject/Umsetzung/base/src/inc.config.php',
      'MyNewProject/Umsetzung/base/src/tpl/welcome.html',
      'MyNewProject/Umsetzung/base/src/MyNewProject/tests',
      'MyNewProject/Umsetzung/base/bin/lib',
      'MyNewProject/Umsetzung/base/build/default',
      'MyNewProject/Umsetzung/base/files/testdata',
      'MyNewProject/Umsetzung/base/htdocs/css',
      'MyNewProject/Umsetzung/base/htdocs/img',
      'MyNewProject/Umsetzung/base/htdocs/js',
      'MyNewProject/Umsetzung/base/htdocs/jsc',
      'MyNewProject/Umsetzung/base/src/MyNewProject',
      'MyNewProject/Umsetzung/base/src/tpl',
      'MyNewProject/Umsetzung/base/bin',
      'MyNewProject/Umsetzung/base/build',
      'MyNewProject/Umsetzung/base/cache',
      'MyNewProject/Umsetzung/base/files',
      'MyNewProject/Umsetzung/base/htdocs',
      'MyNewProject/Umsetzung/base/src',
      'MyNewProject/Umsetzung/base',
      'MyNewProject/Umsetzung/conf',
      'MyNewProject/Umsetzung',
      'MyNewProject'
    );
    $this->assertEquals($exList, $list);
  }
  
  /**
   * @expectedException Psc\System\Exception
   */
  public function testListExceptionNoArchive() {
    $archive = new SimpleRarArchive($this->newFile('notavaible'), $this->finder);
    $archive->listFiles();
  }
  
  public function testExtractArchiveInWhiteSpaceDirectory() {
    $archive = new SimpleRarArchive($this->getFile('IMMERSE.en.rar','gemeines verzeichnis/'), $this->finder);
    
    $list = $archive->listFiles();
    $this->assertEquals(array('How.I.Met.Your.Mother.S07E03.Ducky.Tie.720p.HDTV.x264-IMMERSE.VO-SubCentral.srt'), $list);
  }

}
?>