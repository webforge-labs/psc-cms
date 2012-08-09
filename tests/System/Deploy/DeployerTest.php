<?php

namespace Psc\System\Deploy;

/**
 * @group class:Psc\System\Deploy\Deployer
 */
class DeployerTest extends \Psc\Code\Test\Base {
  
  protected $deployer;
  protected $emptyDeployer;
  protected $deployments;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Deploy\Deployer';
    parent::setUp();

    if (\Psc\PSC::isTravis()) {
      $this->markTestSkipped('zu Env abhängig');
    }
    
    $this->deployments = new \Psc\System\Dir('D:\www\deployments\\');
    
    
    $this->deployer = clone $this->emptyDeployer = new Deployer($this->deployments,
                                        \Psc\PSC::getProjectsFactory()->getProject('psc-cms'),
                                        new \Psc\System\BufferLogger()
                                       );
    $this->deployer->init();
    
    $this->emptyDeployer->addTask(
      $this->emptyDeployer->createTask('CreateAndWipeTarget')
    );
  }
  
  public function testEmptyDeployerDeploysIntoEmptySubDir() {
    $this->deployer = $this->emptyDeployer;
    $this->deployer->deploy();
    
    $pr = $this->deployments->sub('psc-cms/');
    
    $this->assertTrue($pr->exists(), 'Verzeichnis psc-cms existiert nicht in deployments');
    $this->assertTrue($pr->isEmpty(), 'Verzeichnis '.$pr.' ist nicht leer');
  }

  public function testEmptyDeployerDeploysIntoEmptySubDirAllthoughNotEmpty() {
    $this->deployer = $this->emptyDeployer;
    $pr = $this->deployments->sub('psc-cms/');
    
    // create some trash
    $pr->getFile('someNotEmptyFile')->writeContents('sdlfjsldfj');
    $pr->sub('some/sub/dir')->create();
    $this->assertFalse($pr->isEmpty());
    
    // deploy
    $this->deployer->deploy();
    
    // assert empty
    $this->assertTrue($pr->exists(), 'Verzeichnis tiptoi existiert nicht in deployments');
    $this->assertTrue($pr->isEmpty(), 'Verzeichnis ist  nicht leer');
  }
  
  public function testCustomAddedTaskWillBeDeployed() {
    $task = $this->getMock('Psc\System\Deploy\Task', array('run'));
    $task->expects($this->once())->method('run');
    
    $this->deployer->addTask($task);
    
    $this->deployer->deploy();
  }
  
  protected function onNotSuccessfulTest(\Exception $e) {
    if (isset($this->deployer))
      print $this->deployer->getLog();
    throw $e;
  }
}
?>