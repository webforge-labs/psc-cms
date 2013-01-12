<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Psc\System\Deploy\Deployer;

class DeployCommand extends Command {
  
  protected $file;
  protected $outFile;
  
  protected function configure() {
    $this->setName('project:deploy');
    $this->addArgument('mode',self::OPTIONAL | self::IS_ARRAY, 'staging oder normal', array('normal'));
    $this->addOption('deploymentsDir',self::VALUE_REQUIRED);
    //$this->addArgument('class',self::REQUIRED);
    //$this->addOption('out','o',self::VALUE_REQUIRED);
    $this->setDescription('Exportiert das Projekt mit allen Binaries und Sourcen in den Deployments Ordner');
    $this->addOption('changes',null, self::VALUE_REQUIRED | self::VALUE_IS_ARRAY);
  }
  
  protected function doExecute($input, $output) {
    $cliProject = $this->getHelper('project')->getProject();
    $modes = $input->getArgument('mode');
    
    foreach ($modes as $mode) {
      $project = clone $cliProject;
    
      if ($mode === 'staging') {
        $project->setVhostName('tiptoi.staging.ps-webforge.com'); // das ist nicht die url sondern unser target verzeichnis auf pegasus
        $project->setStaging(TRUE);
      } else {
        $project->setVhostName('tiptoi.ps-webforge.com'); // das ist nicht die url sondern unser target verzeichnis auf pegasus
      }
      
      $changes = $input->getOption('changes');
      
      $deployer = new Deployer(
        new Dir($input->getOption('deploymentsDir') ?: 'D:\www\deployments\\'),
        $project,
        $mode,
        $logger = new \Psc\System\EchoLogger()
      );
      
      $deployer->init();
      $deployer->setHostName('pegasus');
      
      if ($mode === 'staging') {
        $deployer->setBaseUrl('tiptoi.staging.ps-webforge.com');
      } else {
        $deployer->setBaseUrl('tiptoi.ps-webforge.com');
      }
      
      $deployer->addTask($deployer->createTask('CreateAndWipeTarget'));
  
      // wir schreiben in das SOURCE changelog
      // fixme: probleme mit utf8
      //if (is_array($changes)) {
      //  $deployer->addTask(
      //    $deployer->createTask('WriteChangelog')
      //      ->setChanges($changes)
      //  );
      //}
      
      $deployer->addTask($deployer->createTask('CopyProjectSources')
                          ->addAdditionalPath('htdocs/upload-manager/')
                          ->addAdditionalPath('htdocs/files/')
                         );
      $deployer->addTask($deployer->createTask('CreateBootstrap')
                          ->addModule('Symfony')
                          ->addModule('Doctrine')
                          ->addModule('PHPExcel')
                          ->addModule('PHPWord')
                          ->addModule('Imagine')
                        ); // bootstrap nach copy project sources damit auto.prepend.php überschrieben wird
      
      $deployer->addTask($deployer->createTask('DeployPscCMS')); // installiert phars und so 
      $deployer->addTask($deployer->createTask('DeployDoctrine'));
      
      $configureApache =
         $mode === 'staging'
         ?
         $deployer->createTask('ConfigureApache')
            ->setServerName('tiptoi.staging.ps-webforge.com')
         :
         $deployer->createTask('ConfigureApache')
            ->setServerName('tiptoi.ps-webforge.com')
            ->setServerAlias(array('tiptoi.ps-webforge.de',
                                   'tiptoi.ps-webforge.net',
                                 )
                          )
        ;
  
      $deployer->addTask($configureApache
                          ->setHtaccess($project->getBuildPath()->getFile('.deploy.htaccess')->getContents())
                        );
  
      //$deployer->addTask(
      //  $deployer->createTask('CommitGitDeployment')
      //    //->setGitDir(new \Webforge\Common\System\Dir('D:\www\deployments.git\tiptoi\\'))
      //    ->setRemote('origin') // $mode === 'staging' ? 'staging' : 'origin'
      //    ->setMessage(count($changes) > 0 ? implode("\n", $changes) : NULL)
      //);
  
      // lokaler build ? (vorher muss dann ins lokale verzeichnis commited werden)
      //$deployer->addTask(
      //  $deployer->createTask('SismoBuild')
      //);
      
      if ($mode === 'staging') {
        $deployer->addTask(
          $deployer->createTask('UnisonSync')
            ->setProfile('automatic.tiptoi.staging.ps-webforge.com@pegasus.ps-webforge.com')
        );
      }
      
      $deployer->deploy();
    }
  }
}
?>