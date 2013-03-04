<?php

namespace Psc\System\Console;

use \Symfony\Component\Console\Input\InputOption,
    \Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    
    Psc\PSC,
    Webforge\Common\String AS S,
    
    Webforge\Common\System\Dir,
    Webforge\Common\System\File
  ;

class CreateJooseCommand extends Command {

  protected function configure() {
    $this
      ->setName('create-joose')
      ->setDescription(
        'Erstellt eine neue Javascript JOOSE Klasse (im psc-cms- Projekt)'
      )
      ->setDefinition(array(
        new InputArgument(
          'class', InputArgument::REQUIRED,
          'Der FQN der Klasse mit . getrennt und ohne . am Anfang. Erste Buchstabe gross'
        ),
        new InputArgument(
          'parent', InputArgument::OPTIONAL,
          'Der Name der Elternklasse (FQN)'
        ),
        new InputArgument(
          'interfaces', InputArgument::OPTIONAL,
          'Eine Liste der Interfaces (FQN) die die Klasse implementiert mit , getrennt'
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
      ->setHelp(
'Erstellt eine neue Joose-Klasse

Beispiel:
'
//.$this->getName().' -c \Psc\CMS\ContentItem  -p \Psc\Object --interfaces \Psc\Special\Listener 
      );
    }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    //$inProject = ($this->hasHelper('project') && ($cliProject = $this->getHelper('project')->getProject()) instanceof \Psc\CMS\Project);
    
    $class = $input->getArgument('class');
    $overwrite = $input->getOption('overwrite');
    $projectn = 'psc-cms';
    $parent = $input->getArgument('parent');
    
    //if (!S::startsWith($class, 'Psc.')) {
    //  $class = 'Psc.'.$class;
    //}
    //if ($parent != NULL && !S::startsWith($parent, 'Psc.')) {
    //  $parent = 'Psc.'.$parent;
    //}
    
    try {
      $project = PSC::getProjectsFactory()->getProject($projectn);
    } catch (\Psc\ProjectNotFoundException $e) {
      $output->writeln("FEHLER: Projekt: '".$projectn."' nicht gefunden");
      return 1;
    }

    $interfaces = array();
    if (($ifs = $input->getArgument('interfaces')) != NULL) {
      foreach(array_map('trim',explode(',',$ifs)) as $if) {
        $interfaces[] = $if;
      }
    }
    
    $classes = $project->getHtdocs()->sub('js/cms/class/');
    $tests = $project->getHtdocs()->sub('js/cms/tests/');
    
    $output->writeln(sprintf("Erstelle '%s' in Projekt '%s' abgeleitet von: '%s'",
                             $class, $project->getName(), $parent));
    
    $classFile = new File($classes.str_replace('.',DIRECTORY_SEPARATOR,$class));
    $classFile->setExtension('js');
    $testFile = new File($tests.str_replace('.',DIRECTORY_SEPARATOR,$class).'Test');
    $testFile->setExtension('js');
    
    if (!$classFile->getDirectory()->isSubdirectoryOf($classes)) {
      $output->writeln('FEHLER: Ermittelte Datei(Class): '.$classFile.' liegt nicht in '.$classes);
      return 1;
    }
    if (!$testFile->getDirectory()->isSubdirectoryOf($tests)) {
      $output->writeln('FEHLER: Ermittelte Datei(Test): '.$testFile.' liegt nicht in '.$tests);
      return 1;
    }
    
    if (($classFile->exists() || $testFile->exists()) && !$overwrite) {
      $output->writeln('FEHLER: Test oder Klasse bestehen bereits und overwrite ist nicht gesetzt. --overwrite benutzen zum Überschreiben');
      return 1;
    }
    
/*
    if ($inProject && !$file->getDirectory()->isSubdirectoryOf($cliProject->getBase())) {
      $output->writeln('FEHLER: Datei erstellen nicht erlaubt! '.$file.' ist nicht in: '.$cliProject->getBase());
      return 1;
    }
*/
$classJS = <<< '__JS__'
Class('%class%', {
  %parent%
  has: {
    //attribute1: { is : 'rw', required: false, isPrivate: true }
  },
  
  after: {
    initialize: function () {
      
    }
  },
  
  methods: {
    toString: function() {
      return "[%class%]";
    }
  }
});
__JS__;

$testJS = <<< '__JS__'
use(['%class%','Psc.Test.DoublesManager'], function() {
  
  module("%class%");
  
  var setup = function (test) {
    //var dm = new Psc.Test.DoublesManager();
    var %scClass% = new %class%({ });
    
    $.extend(test, {%scClass%: %scClass%});
  };

  test("acceptance", function() {
    setup(this);
  
    // this.%scClass%.doSomething();
  });
});
__JS__;

    $parent = $parent != NULL ? sprintf("isa: '%s',\n",$parent) : NULL;
    $output->writeln('Schreibe: '.$classFile.'.');
    $classFile->getDirectory()->create();
    $classFile->writeContents(\Psc\TPL\TPL::miniTemplate($classJS, compact('class','parent')));
      
    if (!$input->getOption('without-test')) {
      $scClass = lcfirst(\Webforge\Common\ArrayUtil::peek(explode('.',$class)));
      $output->writeln('Schreibe: '.$testFile.'.');
      $testFile->getDirectory()->create();
      $testFile->writeContents(\Psc\TPL\TPL::miniTemplate($testJS, compact('class','scClass')));
    }
    
    $output->writeln('finished.');
  }
}
?>