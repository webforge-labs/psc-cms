<?php

namespace Psc\System\Console;

use Webforge\Common\System\File;
use Webforge\Common\System\Dir;

class SismoBuildCommand extends Command {
  
  protected $file;
  protected $outFile;
  
  protected function configure() {
    $this->setName('project:build');
    //$this->addArgument('deploymentsDir',self::OPTIONAL);
    //$this->addArgument('class',self::REQUIRED);
    $this->addOption('force',null,self::VALUE_NONE);
    $this->setDescription('Ruft sismo build für das Projekt auf');
    //$this->addOption('changes',null, self::VALUE_REQUIRED | self::VALUE_IS_ARRAY);
  }
  
  protected function doExecute($input, $output) {
    $cliProject = $this->getHelper('project')->getProject();
    
    $task = new \Psc\System\Deploy\SismoBuildTask($cliProject);
    return $task->run();
  }
}
?>