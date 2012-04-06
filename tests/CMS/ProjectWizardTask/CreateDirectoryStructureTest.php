<?php

namespace Psc\CMS\ProjectWizardTask;

use Psc\CMS\ProjectWizardTask\CreateDirectoryStructure;
use Psc\PSC;

class CreateDirectoryStructureTest extends TestCase {
  
  public function assertPreConditions() {
    $this->assertInstanceOf('Psc\CMS\ProjectWizardTask\CreateDirectoryStructure', $this->task);
    $this->assertInstanceOf('Psc\CMS\ProjectWizard', $this->projectWizard);
  }
  
  public function testRun() {
    parent::testRun(); // ausführen
    
    $testDir = $this->getTestDirectory();
    $stringify = function (Array $dirs, $removeSub) use ($testDir) {
      $dirs = array_values($dirs);
      
      return array_map(function ($dir) use ($testDir, $removeSub) {
        
        return (string) $dir->makeRelativeTo($testDir->sub($removeSub));
      
      }, $dirs);
    };
    
    $expected = $stringify($this->getTestDirectory('expected/')->getDirectories(),'expected/');
    $actual = $stringify($this->getTestDirectory('test/')->getDirectories(), 'test/');
    sort($expected);
    sort($actual);
    
    $diff = array_diff($expected, $actual);
    $this->assertEquals(0, count($diff), "Verzeichnis-Struktur inkomplett: \n".\Psc\A::join($diff, "fehlt: '%s' \n"));
    
    // das ist nochmal der paranoia mode
    // dier erste assertion ist für debugging
    $this->assertEquals($expected,
                        $actual, 
                        'Verzeichnis Struktur nicht gleich',
                        0,
                        10,
                        TRUE
                       );
    //$this->assertTrue($path->exists(), 'Pfad: '.$path.' wurde nicht angelegt');
  }
}
?>