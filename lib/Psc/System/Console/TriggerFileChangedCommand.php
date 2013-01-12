<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    
    Psc\PSC,
    Webforge\Common\System\Dir,
    Webforge\Common\System\File,
    Psc\System\System,
    Psc\Code\Code

  ;

/**
 * Trigger File Changed
 *
 * der Befehl ist dafür da, zu überprüfen ob die Datei neu kompiliert werden muss
 * Diesen Trigger kann man z. B. an seinen Editor hängen, der mit dem vollen Pfad das aufruft
 * das cms macht dann alles weitere im Hintergrund. Die Library wird quasi informiert, dass ich seine Datei geändert hat, die möglicherweise etwas auslösen soll (neucompilieren, syntaxcheck, etc)
 */
class TriggerFileChangedCommand extends Command {

  protected function configure() {
    $this
      ->setName('trigger-file-changed')
      ->setDescription(
        'Teilt der Library mit, dass sich diese Datei geändert haben kann. (zum Neukompilieren etc..)'
      )
      ->setDefinition(array(
        new InputArgument(
          'file', InputArgument::REQUIRED,
          'der volle Pfad zur PHP-Datei'
        ),
        new InputOption(
          'compile','c',InputOption::VALUE_NONE,
          'Compiled das Projekt direkt'
        )
      ))
      ->setHelp(
        'Teilt der Library mit, dass sich diese Datei geändert haben kann. (zum Neukompilieren etc..)ine Library inklusive einer Bootstrap.

Beispiel: '.$this->getName().' triger-file-changed D:\www\psc-cms\Umsetzung\base\src\psc\class\Psc\ICTS\SiteTemplate.php '
      );
    }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $file = $input->getArgument('file');
    $compile = (bool) $input->getOption('compile');
    
    if ($file == '') {
      $output->writeln('Datei nicht angegeben');
      return;
    }
    
    $file = new File($file);
    
    /* Performance: kein file_exists() */
    //if (!$file->exists()) {
    //  $output->writeln('Datei existiert nicht.');
    //  return;
    //}
    
    /* Der Source Path vom CMS */
    $cp = PSC::getProjectsFactory()->getProject('psc-cms')->getClassPath();
    //$output->writeln($file->getDirectory().' subDirOf ');
    //$output->writeln((string) $cp);

    /* Die Datei ist für uns (erstmal) nur interessant, wenn Sie im PSC-CMS Verzeichnis liegt
       Sie ist damit also eine SRC Datei und triggered das compilieren des Phars
    */
    if ($file->getDirectory()->isSubdirectoryOf($cp) || $file->getDirectory()->equals($cp)) {
      
      PSC::getEventManager()->dispatchEvent('Psc.SourceFileChanged',
                                            (object) array('file'=>$file,'compile'=>$compile),
                                            $this
                                           );
      
      if ($compile) {
        $output->writeln('SourceDatei in psc-cms '.$file->getName().' wurde compiled.');
      } else {
        $output->writeln('SourceDatei in psc-cms '.$file->getName().' wurde als geändert markiert.');
      }
    } else {
      $output->writeln('SourceDatei '.$file.' wurde nicht markiert.');
    }
  }
}
?>