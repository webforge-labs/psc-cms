<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputOption;
use  Symfony\Component\Console\Input\InputArgument;
use  Symfony\Component\Console\Input\InputInterface;
use  Symfony\Component\Console\Output\OutputInterface;

use  Psc\PSC;
use  Webforge\Common\System\Dir;
use  Webforge\Common\System\File;
use  Psc\System\System;
use  Psc\Code\Code;
use  Psc\A;

use  Psc\Code\Build\LibraryBuilder;

class BuildPharCommand extends Command {

  protected function configure() {
    $this
      ->setName('build-phar')
      ->setDescription(
        'Erstellt ein Phar für eine Library'
      )
      ->setDefinition(array(
        new InputOption(
          'lib', 'l', InputOption::VALUE_OPTIONAL,
          'Kürzel der Library oder Name des Moduls',
          'psc-cms'
        ),
        new InputOption(
          'out', 'o', InputOption::VALUE_OPTIONAL,
          'der volle Pfad zur Output-Datei  Output-Verzeichnis für nicht phar-builds (andere libraries)',
          NULL
        ),
        new InputOption(
          'install', 'i', InputOption::VALUE_OPTIONAL,
          'In welchem Projekt soll die Library installiert werden? (kopiert)',
          NULL
        ),
        new InputOption(
          'check', 'c', InputOption::VALUE_NONE,
          'Wenn aktiviert wird vorher geprüft ob compilen nötig ist'
        ),
        new InputOption(
          'pre-boot-file', NULL, InputOption::VALUE_REQUIRED,
          'Der Content der Datei wird in der Bootstrap bei %%PRE-BOOT%% ersetzt. (siehe bootstrap.phar.php) '
        ),
        new InputOption(
          'post-boot-file', NULL, InputOption::VALUE_REQUIRED,
          'Der Content der Datei wird in der Bootstrap bei %%POST-BOOT%% ersetzt. (siehe bootstrap.phar.php) '
        ),
        new InputOption(
          'build-name', NULL, InputOption::VALUE_REQUIRED,
          'Der Name des spezifischen Builds für other-library-builds. muss in build/$buildName eine dependencies.php haben '
        )
      ))
      ->setHelp(
        'Erstellt ein Phar für eine Library inklusive einer Bootstrap.

Beispiel: '.$this->getName().' --lib=psc-cms -o D:\www\psc-cms-bin\psc-cms.phar.gz'
      );
    }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $lib = $input->getOption('lib');
    $checkCompilingNeeded = $input->getOption('check') == TRUE;
    
    // not used
    $postBootFile = ($pbf = $input->getOption('post-boot-file')) != NULL ? new File($pbf) : NULL;
    $preBootFile = ($pbf = $input->getOption('pre-boot-file')) != NULL ? new File ($pbf) : NULL;
    
    $buildName = $input->getOption('build-name') ?: 'default';
    
    $out = $input->getOption('out');
    if ($out != NULL) {
      $out = new File($out);
    }
    $output->writeln("Building Phar for '".$lib."'");
    $module = $libProject = NULL;
    
    if ($lib == 'psc-cms') {
      $libProject = PSC::getProject();
      $out = $out ?: $libProject->getInstallPharFile();
    } elseif(PSC::getProject()->isModule($lib)) {
      $module = PSC::getProject()->getModule($lib);
      $out = $out ?: new File(PSC::get(PSC::ROOT), $module->getLowerName().'.phar.gz');
    } else {
      throw new Exception('Building a Projects is not implemented anymore');
      $libProject = PSC::getProjectsFactory()->getProject($lib);
      $out = $out?: new Dir($libProject->getBin());
    }
  
    /*
      für alle weiteren Projekte verzweigen wir hier also auf die Project-Klasse
      (da kann man sich dann auch mal entscheiden ANT zu nehmen, oder was man auch immer mag)
    */
    if (isset($libProject) && $lib != 'psc-cms') {
      $output->writeln("  Compiling to ".$out."... ");
      $libProject->buildPhar($out, $checkCompilingNeeded, $buildName);
      $output->writeln("finished.");
      return 0;
    } elseif(isset($module)) {
      $output->writeln("  Compiling Module to ".$out."... ");
      $pharBuilder = $module->buildPhar($out, $checkCompilingNeeded, $buildName);
      if ($input->getOption('verbose') == TRUE) {
        print \Psc\A::join($pharBuilder->getPhar()->getLog(),"    [phar] %s\n");
      }
      $output->writeln('finished.');
      return 0;
    } else {
      $libraryBuilder = new LibraryBuilder($libProject, $logger = new \Psc\System\EchoLogger());
      
      if (!$checkCompilingNeeded || $libraryBuilder->isCompilingNeeded()) {
        $libraryBuilder->compile($out);
        $libraryBuilder->resetCompilingNeeded();
      }
      
      if (($projectName = $input->getOption('install')) != NULL) {
        throw new Exception('Installing to a project is not implemented anymore');
        
        $project = PSC::getProjectsFactory()->getProject($projectName);
        $installPhar = $project->getInstallPharFile();
        $logger->writeln("  Installiere Phar in '".$project->getName()."' kopiere nach ".$installPhar." ");
        $out->copy($installPhar);
      }
      
      $output->writeln('finished.');
      return 0;
    }
  }
}
?>