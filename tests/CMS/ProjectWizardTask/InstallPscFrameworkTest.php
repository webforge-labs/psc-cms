<?php

namespace Psc\CMS\ProjectWizardTask;

use Psc\CMS\ProjectWizardTask\InstallPscFramework;

/**
 * @group class:Psc\CMS\ProjectWizardTask\InstallPscFramework
 */
class InstallPscFrameworkTest extends TestCase {
  
  public function assertPreConditions() {
    parent::assertPreConditions();
    
    $this->assertTrue($this->getTestDirectory()->sub('test/MyNewProject/Umsetzung/base/src/')->exists(),'Pre-Condition: fixture wurde nach test kopiert, schlägt fehl');
  }
  
  public function testRun() { // AcceptanceTest
    parent::testRun();
    
    $this->assertFileExists((string) ($phar = $this->getFile('psc-cms.phar.gz', 'test/MyNewProject/Umsetzung/base/src/')),
                            'phar wurde nicht erstellt');
    
    
    $this->assertFileExists(
                            (string) ($autoPrepend = $this->getFile('auto.prepend.php', 'test/MyNewProject/Umsetzung/base/src/')),
                            'base/src/auto.prepend.php wurde nicht erstellt'
                            );

    $this->assertFileExists(
                            (string) ($cliPHP = $this->getFile('cli.php', 'test/MyNewProject/Umsetzung/base/bin/')),
                            'base/bin/cli.php wurde nicht erstellt'
                            );

    $this->assertFileExists(
                            (string) ($cliBAT = $this->getFile('cli.bat', 'test/MyNewProject/Umsetzung/base/bin/')),
                            'base/bin/cli.bat wurde nicht erstellt'
                            );

    $this->assertFileExists(
                            (string) ($projectConsole = $this->getFile('ProjectConsole.php', 'test/MyNewProject/Umsetzung/base/src/MyNewProject/')),
                            'base/bin/cli.bat wurde nicht geschrieben'
                            );
  }
}
?>