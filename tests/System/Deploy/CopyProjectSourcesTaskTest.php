<?php

namespace Psc\System\Deploy;

/**
 * @group class:Psc\System\Deploy\CopyProjectSources
 */
class CopyProjectSourcesTaskTest extends \Psc\Code\Test\Base {
  
  protected $task, $targetProject, $testDir;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Deploy\CopyProjectSources';
    parent::setUp();
    $this->testDir = $this->getTestDirectory('target/')->create()->wipe();
    
    $this->sourceDir = $this->getTestDirectory('source/');
    $this->sourceDir->sub('base/src/')->create()->getFile('sample.php')->writeContents('<?php echo "sample"; ?>');
    $this->sourceDir->sub('base/src/testProject')->create()->getFile('Class1.php')->writeContents('<?php class Class1 {} ?>');
    $this->sourceDir->sub('base/src/testProject')->create()->getFile('Class2.php')->writeContents('<?php class Class2 {} ?>');
    $this->sourceDir->sub('base/bin/')->create()->getFile('sample.binary.php')->writeContents('<?php execute() ?>');
    $this->sourceDir->sub('base/src/tpl/')->create()->getFile('template.html')->writeContents('<html><body>Jay</body></html>');
    $this->sourceDir->sub('base/htdocs/')->create()->getFile('index.php')->writeContents('<html><body><!-- index.php bootstrapping --></body></html>');
    $this->sourceDir->sub('base/htdocs/')->create()->getFile('api.php')->writeContents('<?php new Main(); ?>');
    $this->sourceDir->sub('base/htdocs/css/')->create()->getFile('default.css')->writeContents('body { font-family: Arial; }');
    $this->sourceDir->sub('base/htdocs/js/')->create()->getFile('Main.js')->writeContents("Class('Psc.UI.Main'{});");
    $this->sourceDir->sub('base/htdocs/img/')->create()->getFile('spinner.gif')->writeContents("0ss9fYL§$09sd8fa");
    $this->sourceDir->sub('base/files/testdata/fixtures/')->create();
    
    $this->sourceProject = $this->doublesManager->Project('test-project', $this->sourceDir)->build();
    $this->targetProject = $this->doublesManager->Project('test-project', $this->testDir)->build();
    
    $this->task = new CopyProjectSourcesTask($this->sourceProject, $this->targetProject);
  }
  
  public function testAcceptance() {
    $this->task->run();
    
    $this->assertFileExists((string) $this->testDir->sub('base/src/')->getFile('sample.php'));
    $this->assertFileExists((string) $this->testDir->sub('base/src/testProject/')->getFile('Class1.php'));
    $this->assertFileExists((string) $this->testDir->sub('base/src/testProject/')->getFile('Class2.php'));
    
    $this->assertFileExists((string) $this->testDir->sub('base/bin/')->getFile('sample.binary.php'));
    //$this->assertFileExists((string) $this->testDir->sub('base/bin/')->getFile('phpunit.xml'));
    $this->assertFileExists((string) $this->testDir->sub('base/src/tpl/')->getFile('template.html'));
    $this->assertFileExists((string) $this->testDir->sub('base/htdocs/img/')->getFile('spinner.gif'));
    $this->assertFileExists((string) $this->testDir->sub('base/htdocs/js/')->getFile('Main.js'));
    $this->assertFileExists((string) $this->testDir->sub('base/htdocs/css/')->getFile('default.css'));
  }
}
?>