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
    
    $this->deployments = new \Psc\System\Dir('D:\www\deployments\\');
    
    
    $this->deployer = clone $this->emptyDeployer = new Deployer($this->deployments,
                                        \Psc\PSC::getProjectsFactory()->getProject('tiptoi'),
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
    
    $tiptoi = $this->deployments->sub('tiptoi/');
    
    $this->assertTrue($tiptoi->exists(), 'Verzeichnis tiptoi existiert nicht in deployments');
    $this->assertTrue($tiptoi->isEmpty(), 'Verzeichnis ist nicht leer');
  }

  public function testEmptyDeployerDeploysIntoEmptySubDirAllthoughNotEmpty() {
    $this->deployer = $this->emptyDeployer;
    $tiptoi = $this->deployments->sub('tiptoi/');
    
    // create some trash
    $tiptoi->getFile('someNotEmptyFile')->writeContents('sdlfjsldfj');
    $tiptoi->sub('some/sub/dir')->create();
    $this->assertFalse($tiptoi->isEmpty());
    
    // deploy
    $this->deployer->deploy();
    
    // assert empty
    $this->assertTrue($tiptoi->exists(), 'Verzeichnis tiptoi existiert nicht in deployments');
    $this->assertTrue($tiptoi->isEmpty(), 'Verzeichnis ist  nicht leer');
  }
  
  public function testCustomAddedTaskWillBeDeployed() {
    $task = $this->getMock('Psc\System\Deploy\Task', array('run'));
    $task->expects($this->once())->method('run');
    
    $this->deployer->addTask($task);
    
    $this->deployer->deploy();
  }
  
  protected function onNotSuccessfulTest(\Exception $e) {
    print $this->deployer->getLog();
    throw $e;
  }
}
?>