<?php

namespace Psc\System\Console;

use \Symfony\Component\Console\Input\InputOption,
    \Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    
    Psc\Code\Generate\TestCreater,
    Psc\Code\Generate\GClass,
    Psc\Code\Generate\ClassWritingException,
    Psc\PSC,
    
    Webforge\Common\System\Dir,
    Webforge\Common\System\File
  ;

class CreateTestCommand extends Command {

  protected function configure() {
    $this
      ->setName('create-test')
      ->setDescription(
        'Erstellt einen Test-Stub für eine bestehende Klasse'
      )
      ->setDefinition(array(
        new InputArgument(
          'class', InputArgument::REQUIRED,
          'Der FQN der Klasse mit \ am Anfang'
        ),
        new InputArgument(
          'template', InputArgument::OPTIONAL,
          'Template-Type des Tests: normal|acceptance|db',
          'normal'
        ),
        new InputOption(
          'overwrite','w',InputOption::VALUE_NONE,
          'Ist dies gesetzt wird der Test überschrieben, egal welchen Inhalt er hat'
        )
      ))
      ->setHelp('Erstellt einen neuen Test-Stub anhand eines Templates');
    }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $project = $this->getHelper('project')->getProject();
    
    $class = new GClass();
    $class->setName($input->getArgument('class'));

    // check ob ClassName stimmt
    if (mb_strpos(trim($class->getNamespace(),'\\'), trim($project->getNamespace(),'\\')) !== 0) {
      $output->writeln("FEHLER: In dieser Console können nur Tests für das Projekt '".$project->getName()."' erstellt werden.");
      return 1;
    }
    
    //$cFile = $project->getClassFile($class->getName());
    //
    //if (!$cFile->exists()) {
    //  $output->writeln('Die Datei: '.$cFile.' fuer die Klasse: '.$class->getName().' konnte nicht gefunden werden');
    //  return 1;
    //}
    
    $output->writeln('Erstelle Test-Stub fuer: '.$class->getName().' in projekt: '.$project->getName());
    $overwrite = $input->getOption('overwrite') == TRUE ? TestCreater::OVERWRITE : FALSE;
    
    try {
      $tc = new TestCreater($class, $project->getTestsPath());
      
      if (($cb = $project->getConfiguration()->get(array('TestCreater', $input->getArgument('template')))) != NULL) {
        $output->writeln('Benutze Template / Configuration: '.$input->getArgument('template'));
        $tc->setCreateCallback($cb);
      }
      
      $file = $tc->create($overwrite);
      
      // notices vom TestCreater
      $output->writeln($tc->getLog());
      
    } catch (ClassWritingException $e) {
      if ($e->getCode() == ClassWritingException::OVERWRITE_NOT_SET) {
        $output->writeln('Test nicht erstellt! Datei ist vorhanden: '.$e->writingFile);
        return;
      }
    }
    
    $output->writeln('Test geschrieben in: '.$file);
    $output->writeln('finished.');
  }
}
?>