<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Psc\System\Deploy\Deployer;
use Psc\CMS\Project;
use Webforge\Framework\Container as WebforgeContainer;

/**
 * 
  protected function initProperties($mode) {
    if ($mode === 'staging') {
      $this->hostName = 'pegasus';
      $this->baseUrl = 'tiptoi.pegasus.ps-webforge.net';
      $this->vhostName = 'tiptoi.pegasus.ps-webforge.net';
      $this->staging = TRUE;
      $this->variant = 'staging';
    } else {
      $this->hostName = 'andromeda';
      $this->baseUrl = 'tiptoi.ps-webforge.com';
      $this->vhostName = 'tiptoi.andromeda.ps-webforge.net';
      $this->staging = FALSE;
    }
  }
*/
abstract class DeployCommand extends Command {
  
  protected $file;
  protected $outFile;
  
  protected $mode;
  
  /* properties */
  protected $hostName;
  protected $baseUrl;
  protected $vhostName;
  protected $staging = FALSE;
  
  protected $variant = NULL;
  /* /properties */
  
  protected function configure() {
    $this->setName('project:deploy');
    $this->addArgument('mode',self::OPTIONAL | self::IS_ARRAY, 'staging oder normal', array('normal'));
    $this->addOption('deploymentsDir',self::VALUE_REQUIRED);
    //$this->addArgument('class',self::REQUIRED);
    //$this->addOption('out','o',self::VALUE_REQUIRED);
    $this->setDescription('Exportiert das Projekt mit allen Binaries und Sourcen in den Deployments Ordner');
    $this->addOption('changes',null, self::VALUE_REQUIRED | self::VALUE_IS_ARRAY);
  }
  
  abstract protected function initProperties($mode);
  
  protected function initProject(Project $project) {
    $project->setVhostName($this->vhostName); // das ist nicht die url sondern unser target verzeichnis auf pegasus
    $project->setStaging($this->staging);
  }
  
  abstract protected function initTasks(Deployer $deployer, Project $project, $mode, WebforgeContainer $container);

  protected function createDeployer(Dir $deployments, Project $project, $mode, WebforgeContainer $container) {
    $deployer = new Deployer(
      $deployments,
      $container,
      $project,
      $this->variant,
      $logger = new \Psc\System\EchoLogger()
    );
    
    $deployer->init();
    $deployer->setHostName($this->hostName);
    
    return $deployer;
  }
  
  protected function updateComposer($project) {
    system('cd '.$project->getVendor()->up().' && composer update --dev');
  }
  
  protected function doExecute($input, $output) {
    $cliProject = $this->getHelper('project')->getProject();
    $modes = $input->getArgument('mode');

    $container = new WebforgeContainer();
    $container->initLocalPackageFromDirectory(new Dir(__DIR__.DIRECTORY_SEPARATOR));
    
    foreach ($modes as $mode) {
      $project = clone $cliProject;
      
      $this->initProperties($mode, $project);
      $this->initProject($project);
      
      $deployer = $this->createDeployer(
        new Dir($input->getOption('deploymentsDir') ?: 'D:\www\deployments\\'),
        $project,
        $mode,
        $container
      );
      
      $deployer->setBaseUrl($this->baseUrl);
      
      $this->updateComposer($project);
      
      $this->initTasks($deployer, $project, $mode, $container);
      
      $deployer->deploy();
      
      $this->afterDeploy($deployer, $project, $mode, $container);
    }
  }
  
  protected function afterDeploy(Deployer $deployer, Project $project, $mode, WebforgeContainer $container) {
  }
}
?>