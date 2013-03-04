<?php

namespace Psc\System\Console;

use \Symfony\Component\Console\Input\InputOption,
    \Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    
    Psc\Code\Generate\GClass,
    Psc\Code\Generate\GMethod,
    Psc\Code\Generate\GProperty,
    Psc\Code\Generate\ClassWriter,
    Psc\Code\Generate\ClassWritingException,
    Psc\Code\Generate\TestCreater,
    Psc\PSC,
    
    Webforge\Common\System\Dir,
    Webforge\Common\System\File
  ;

class CreateClassCommand extends Command {

  protected function configure() {
    $this
      ->setName('create-class')
      ->setDescription(
        'Erstellt eine neue Klasse'
      )
      ->setDefinition(array(
        new InputArgument(
          'class', InputArgument::REQUIRED,
          'Der FQN der Klasse mit \ am Anfang'
        ),
        //new InputArgument(
        //  'projekt', InputArgument::OPTIONAL,
        //  'Der Name des Projektes in dem die Klasse angelegt werden soll (psc-cms, tiptoi, GREG)',
        //  'psc-cms'
        //),
        new InputArgument(
          'parent', InputArgument::OPTIONAL,
          'Der Name der Elternklasse (FQN) '
        ),
        new InputArgument(
          'interfaces', InputArgument::OPTIONAL,
          'Eine Liste der Interfaces (FQN) die die Klasse implementiert mit , getrennt'
        ),
        new InputArgument(
          'template', InputArgument::OPTIONAL,
          'Der Name des Templates, welches für den PHP Code benutzt wird (bis jetzt: standard und exception)'
        ),
        new InputOption(
          'overwrite','w',InputOption::VALUE_NONE,
          'Ist dies gesetzt wird die Datei überschrieben, egal welchen Inhalt sie hat'
        ),
        new InputOption(
          'without-test','',InputOption::VALUE_NONE,
          'Wenn gesetzt wird kein Test erzeugt'
        ),

      ))
      ->setHelp('Erstellt eine neue Klasse anhand eines Templates');
  }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $cliProject = $this->getHelper('project')->getProject();
    
    $cname = $input->getArgument('class');
    if (\Webforge\Common\String::endsWith($cname,'.php')) {
      $cname = mb_substr($cname, 0, -4);
    }
    $cname = trim($cname,';:');
    
    $class = new GClass();
    $class->setName($cname);
    
    // check ob ClassName stimmt
    if (mb_strpos(trim($class->getNamespace(),'\\'), trim($cliProject->getNamespace(),'\\')) !== 0) {
      $output->writeln("FEHLER: In dieser Console können nur Klassen für das Projekt '".$cliProject->getName()."' erstellt werden. Erwarteter Namespace ist: '".trim($cliProject->getNamespace(),'\\')."' uebergeben wurde: '".trim($class->getNamespace(),'\\')."'");
      return 1;
    } 
    
    if (($p = $input->getArgument('parent')) != NULL) {
      $class->setParentClass(new GClass($p));
    }

    if (($ifs =$input->getArgument('interfaces')) != NULL) {
      foreach(array_map('trim',explode(',',$ifs)) as $if) {
        $class->addInterface(new GClass($if));
      }
    }
    
    $class->createAbstractMethodStubs();
    
    $overwrite = $input->getOption('overwrite') == TRUE ? ClassWriter::OVERWRITE : FALSE;
    
    $file = $cliProject->getClassFile($class->getName());
    $output->writeln(sprintf("Erstelle '%s' in Projekt '%s' abgeleitet von: '%s'",
                             $class->getName(), $cliProject->getName(), $p));

    if (!$file->getDirectory()->isSubdirectoryOf($cliProject->getBase())) {
      $output->writeln('FEHLER: Datei erstellen nicht erlaubt! '.$file.' ist nicht in: '.$cliProject->getBase());
      return 1;
    }
    
    $file->getDirectory()->create();
    $imports = array();
    
    try {
      $writer = new ClassWriter();  
      $writer->setClass($class);
      $output->writeln('Schreibe: '.$file.'.');
      $writer->write($file, $imports, $overwrite);
    } catch (ClassWritingException $e) {
      if ($e->getCode() == ClassWritingException::OVERWRITE_NOT_SET) {
        $output->writeln(sprintf('Datei nicht geschrieben! Datei %s ist vorhanden. --overwrite benutzen, um zu überschreiben',$file));
        return;
      }
    }
    
    if (!$input->getOption('without-test') && $cliProject->shouldAutomaticTestCreated($class)) {
      $output->writeln('Erstelle Test-Stub.');
      $tc = new TestCreater($class,$cliProject->getTestsPath());
      $tc->create();
    }
    
    $output->writeln('finished.');
    return 0;
  }
}
?>