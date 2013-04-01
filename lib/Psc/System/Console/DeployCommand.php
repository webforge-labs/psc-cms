<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Psc\System\Deploy\Deployer;
use Psc\CMS\Project;
use Webforge\Common\Preg;
use Webforge\Framework\Container as WebforgeContainer;
use Psc\DateTime\TimeBenchmark;

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
    $this->server = 'www-data@pegasus.ps-webforge.com';
  }
*/
abstract class DeployCommand extends Command {
  
  const OK = 0;
  
  protected $file;
  protected $outFile;
  
  protected $mode;
  
  protected $withoutTest = FALSE;
  
  /* properties */
  protected $hostName;
  protected $baseUrl;
  protected $vhostName;
  protected $staging = FALSE;
  
  protected $variant = NULL;
  
  /**
   * @var string server fqn
   */
  protected $server;
  
  protected $remoteVhostsDir = '/var/local/www/';
  /* /properties */
  
  protected function configure() {
    $this->setName('project:deploy');
    $this->addArgument('mode',self::OPTIONAL | self::IS_ARRAY, 'staging oder normal', array('normal'));
    $this->addOption('deploymentsDir',self::VALUE_REQUIRED);
    //$this->addArgument('class',self::REQUIRED);
    $this->addOption('without-test','',self::VALUE_NONE);
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
    $this->out('[DeployCommand] ** local update Composer');
    system('SET COMPOSER_ROOT_VERSION=dev-master && cd '.$project->getVendor()->up().' && composer update -v --dev');
    $this->br();
  }

  protected function createWebforgeContainer() {
    if (isset($GLOBALS['env']['container'])) {
      return $GLOBALS['env']['container']->webforge;
    } else {
      $container = new WebforgeContainer();
      $container->initLocalPackageFromDirectory(new Dir(getcwd().DIRECTORY_SEPARATOR));

      return $container;
    }
  }
  
  protected function doExecute($input, $output) {
    $bench = new TimeBenchmark();
    $this->withoutTest = $input->getOption('without-test');
    $cliProject = $this->getHelper('project')->getProject();
    $modes = $input->getArgument('mode');

    $container = $this->createWebforgeContainer();
    
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
      
      $this->remoteSync($mode);
      $this->remoteUpdateComposer($mode, $project);
      $this->remoteUpdateDB($mode, $project);
      $this->remoteRunTests($mode, $project);
      
      $this->afterDeploy($deployer, $project, $mode, $container, $input, $output);
      $this->info('deployment finished in '.$bench->stop());
      
      return 0;
    }
  }
  
  protected function remoteSync($mode) {
    if ($mode === 'normal') {
      $this->info('I will wait for you to sync now....');
      
      if (!$this->confirm('Did you synced and should I progress now?')) {
        throw $this->exitException('You have to sync, composer update and update db by yourself', 10);
      }
    }
    $this->br();
  }
  
  protected function remoteUpdateComposer($mode, $project) {
    $this->out('[DeployCommand] ** remote Install Composer');
    if ($mode === 'staging' || $this->confirm('Do you want to install with composer?')) {
      $cmd = 'export COMPOSER_ROOT_VERSION=dev-master; /usr/local/sbin/composer --optimize-autoloader --dev %s';
      $src = $project->loadedFromPackage ? '.' : 'base/src';
      
      $install = $this->remoteExec(
        sprintf($cmd, 'install'),
        $src
      );
      
      if ($install != 0 && $this->confirm('Fck Composer... Do you want to update with composer?')) {
        $this->remoteExec(
          sprintf($cmd, 'update'),
          $src
        );
      }
    }
    $this->br();
  }
  
  protected function getRemoteDBCon($mode) {
    return $mode === 'staging' ? 'tests' : 'default';
  }
  
  protected function remoteUpdateDB($mode, $project) {
    $this->out('[DeployCommand] ** remote Update DB');
    
    $con = $this->getRemoteDBCon($mode);
    $out = '';
    $bin = $project->loadedFromPackage ? 'bin/' : 'base/bin/';
    $this->remoteExec(sprintf('./cli.sh orm:update-schema --con="%s"', $con), $bin, $out);
    if (!Preg::match($out, '/nothing to do/')) {
      if ($this->confirm('Do you want to force update the schema? (see above for changes)')) {
        $this->remoteExec(sprintf('./cli.sh orm:update-schema --force --con="%s"', $con), $bin);
      }
    }
    
    $this->br();
  }
  
  protected function remoteRunTests($mode, $project) {
    $bin = $project->loadedFromPackage ? 'bin/' : 'base/bin/';
    $this->out('[DeployCommand] ** remote Run Tests');
    if ($mode === 'staging' && !$this->withoutTest) {
      $this->remoteExec('phpunit', $bin);
    }
    $this->br();
  }

  protected function getRemoteVhostPath($vhostName, $sub) {
    return $this->remoteVhostsDir.$vhostName.'/'.trim($sub, '/').'/';
  }
  
  protected function remoteExec($cmd, $in, &$output = NULL) {
    $cmd = sprintf('ssh %s "cd %s && export PSC_CMS=/var/local/www/psc-cms-bin/; export WEBFORGE=/var/local/www/.webforge/; %s"', $this->server, $this->getRemoteVhostPath($this->vhostName, $in), $cmd);
    $this->comment($cmd);
    $ret = NULL;
    
    $output = system($cmd, $ret);
    
    return $ret;
  }
  
  protected function afterDeploy(Deployer $deployer, Project $project, $mode, WebforgeContainer $container, $input, $output) {
  }
}
?>