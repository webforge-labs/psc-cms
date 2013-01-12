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

class RunHitchGeneratorCommand extends Command {

  protected function configure() {
    $this
      ->setName('hitch:generate')
      ->setDescription(
        'Erstellt die Hitch Entities für das Projekt'
      )
      ->setDefinition(array(
        new InputOption(
          'overwrite','w',InputOption::VALUE_NONE,
          'Ist dies gesetzt werden die Dateien überschrieben, egal welchen Inhalt sie haben! (ACHTUNG)'
        )
      ))
      ->setHelp(
//.$this->getName().' -c \Psc\CMS\ContentItem  -p \Psc\Object --interfaces \Psc\Special\Listener
'Erstellt die Hitch Entities für das Projekt'
      );
    }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $overwrite = $input->getOption('overwrite') === TRUE;
    $project = PSC::getProject();
    
    $c = $project->getNamespace().'\\HitchGenerator';
    //$output->writeln('Suche Klasse: '.$c);
    $generator = new $c;
    $generator->setOverwrite($overwrite);
    $generator->init();
    $output->writeln('Erstelle Entities:');
     
    try {
    foreach ($generator->generate() as $file=>$class) {
      if ($overwrite) {
        print sprintf("  Ueberschreibe: %s\n",$file);
      } else {
        print sprintf("  Schreibe: %s\n",$file);
      }
    }
    } catch (\Psc\Hitch\GeneratorOverwriteException $e) {
      $output->writeln('FEHLER: Die Entities wurden bereits erzeugt, um die Entities neu zu erzeugen --overwrite oder -w benutzen.');
    }
  }
}
?>