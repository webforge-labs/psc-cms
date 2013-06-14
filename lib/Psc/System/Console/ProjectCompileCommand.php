<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Psc\Code\Generate\TestCreater;
use Psc\Code\Generate\GClass;
use Psc\Code\Generate\ClassWritingException;
use Psc\PSC;

use Webforge\Common\System\Dir;
use Webforge\Common\System\File;


class ProjectCompileCommand extends \Psc\System\Console\Command {

  protected function configure() {
    $this
      ->setName('project:compile')
      ->setDescription(
        'ruft compile() für das Project auf'
      )
      ->setDefinition(array(
      ));
      
    $this->addOption('con','',self::VALUE_REQUIRED);
  }
  
  protected function doCompile(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output) {
    return $this->doDefaultCompile($input, $output);
  }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    parent::execute($input, $output);
    try {
      $this->doCompile($input, $output);

      $output->writeln('validating...');
      $code = $this->callCommand('orm:validate-schema',
                                 array(),
                                 $output
                                );
      
      
      if (($con = $input->getOption('con')) != NULL) {
        $output->writeln('updating schema...');
        $output->writeln('');
        try {
          $code = $this->callCommand('orm:update-schema',
                                   array('--force'=>true,
                                         '--con'=>$con
                                         ),
                                   $output
                                  );
        } catch (\PDOException $e) {
          $this->comment('PDOException für folgende Befehle');
          $output->writeln($this->callCommand('orm:update-schema',
                                   array('--force'=>false,
                                         '--con'=>$con
                                         ),
                                   $output
                                  )
                          );
          throw $e;
        }
      }
      
    
      $output->writeln('done.');
      return 0;
    
    } catch (\Exception $e) {
      $output->writeln('<error>ERROR: Beim Compilen sind Fehler aufgetreten: '.$e->getMessage().'</error>');
      throw $e;
      return 1;
    }
  }
  
  protected function doDefaultCompile(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output) {
    $module = \Psc\PSC::getProject()->getModule('Doctrine');
    $namespace = \Psc\PSC::getProject()->getNamespace();
    $compilerClass = $namespace.'\Entities\Compiler';
    
    $compiler = new $compilerClass(new \Psc\Doctrine\DCPackage($module, $module->getEntityManager()));
    try {
      $compiler->compile();
    } catch (\Exception $e) {
      $output->write($compiler->getLog());
      throw $e;
    }
    
    $output->write($compiler->getLog());
    return 0;
  }
}
?>