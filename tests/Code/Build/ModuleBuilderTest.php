<?php

namespace Psc\Code\Build;

use \Psc\Code\Build\ModuleBuilder;

class ModuleBuilderTest extends \Psc\Code\Test\Base {
  
  /**
   * @dataProvider provideBuildingModules
   */
  public function testModuleBuilding($moduleName, Array $expectedRelativeEntries) {
    if (PSC::getProject()->getHost() != 'psc-laptop' && PSC::getProject()->getHost() != 'psc-desktop') {
      $this->markTestSkipped('Module Sourcen existieren nicht für Host '.PSC::getProject()->getHost().'');
    }
    
    $module = \Psc\PSC::getProject()->getModule($moduleName);
    
    $pharFile = $this->newFile('Module'.$moduleName.'.phar.gz');
    $pharFile->delete();
    
    $builder = new \Psc\Code\Build\ModuleBuilder($module);
    $builder->buildPhar($pharFile);
    //print $builder->getPhar()->debug();
    
    $this->assertFileExists((string) $pharFile);
    
    $phar = new \Phar($pharFile,
                     \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO
                     );
    $relativeEntries = array();
    foreach ($phar as $path =>$pharFileInfo) {
      $relativeEntries[] = $pharFileInfo->getFileName();
    }
    $this->assertEquals($expectedRelativeEntries, $relativeEntries);
  }
  
  public static function provideBuildingModules() {
    return Array(
      array('PHPWord', array('PHPWord', 'PHPWord.php', 'index.php')),
      array('Hitch', array('Hitch', 'index.php')),
    );
  }
}

?>