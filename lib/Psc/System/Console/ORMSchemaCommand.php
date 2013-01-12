<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psc\PSC;
use Psc\Doctrine\Helper as DoctrineHelper;

/**
 * @TODO could be refactored to use the doctrine internal command
 * (this command is so old, that this command did not exist, when this class was written)
 */
class ORMSchemaCommand extends DoctrineCommand {

  protected function configure() {
    $this
      ->setName('orm:update-schema')
      ->setDescription(
        'Aktualisiert das Datenbank-Schema'
      )
      ->setDefinition(array(
        new InputOption(
          'con', '', InputOption::VALUE_OPTIONAL,
          'Name der Connection',
          'default'
        ),
        new InputOption(
          'force','f',InputOption::VALUE_NONE,
          'Ist dies gesetzt wird der Befehl ausgeführt. Ansonsten wird nur das SQL ausgegeben'
        ),
      ))
      ->setHelp(
                $this->getName().' --force'."\n".
                'Aktualisiert das Datenbank-Schema '
      );
    }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $force = ($input->getOption('force') === TRUE) ? DoctrineHelper::FORCE : NULL;
    $con = $input->getOption('con');

    if ($force == DoctrineHelper::FORCE) {
      $output->writeln('Updating Schema (forced) fuer Connection: '.$con.' ');
    } else {
      $output->writeln("Printing Update-Schema SQL fuer Connection: ".$con);
    }
    
    $em = PSC::getProject()->getModule('Doctrine')->getEntityManager($con);
    
    $output->writeln(DoctrineHelper::updateSchema($force, "\n", $em));
    
    return 0;
  }
}
?>