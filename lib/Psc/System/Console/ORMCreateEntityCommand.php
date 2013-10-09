<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    
    Psc\PSC,
    
    Psc\Doctrine\EntityBuilder,
    Psc\Doctrine\Helper as DoctrineHelper
  ;

/**
 * @TODO eher kein ORM eher CMS-Namespace ;)
 */
class ORMCreateEntityCommand extends Command {

  protected function configure() {
    $this
      ->setName('cms:create-entity')
      ->setDescription(
        'Erstellt ein neues Entity'
      )
      ->setDefinition(array(
        new InputArgument(
          'name', InputArgument::REQUIRED,
          'Der Name des Entites in CamelCase (erster Buchstabe Groß).'
        ),
        new InputArgument(
          'tableName',InputArgument::OPTIONAL,
          'Ist dies gesetzt wird der TableName manuell gesetzt'
        ),
        new InputOption(
          'with-repository',NULL,InputOption::VALUE_OPTIONAL,
          'Ist dies gesetzt wird das Repository für das Entity erstellt',
          'true'
        ),
        new InputOption(
          'with-entity',NULL,InputOption::VALUE_OPTIONAL,
          'Ist dies gesetzt wird das das Entity erstellt. (praktisch wenn man nur repository braucht)',
          'true'
        ),
        new InputOption(
          'overwrite','w',InputOption::VALUE_NONE,
          'Ist dies gesetzt werden bestehende Dateien überschrieben'
        ),
        new InputOption(
          'version1','',InputOption::VALUE_NONE,
          'Soll ein altes Entity erzeugt werden? (v1)'
        )
      ))
      ->setHelp(
          $this->getName().' MyNewEntity'."\n".
          'Erstellt das neue "MyNewEntity" - Entity'.PHP_EOL.
          $this->getName().' MyNewEntity --with-repository=false'."\n".
          'Erstellt das neue "MyNewEntity" - Entity aber ohne das Repository'
      );
    }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $overwrite = $input->getOption('overwrite') ? EntityBuilder::OVERWRITE : NULL;
    $builder = new EntityBuilder($input->getArgument('name'), $this->getDoctrinePackage()->getModule());
    
    $entity = ($input->getOption('with-entity') != 'false');
    $builder->setWithRepository($repo = ($input->getOption('with-repository') != 'false'));
    
    if ($input->getOption('version1')) {
      $builder->buildDefault();
    } else {
      $builder->buildDefaultV2();
    }
    
    if ($entity) {
      if ($table = $input->getArgument('tableName')) {
        $builder->setTableName($table);
      }

      $file = $builder->write(NULL, $overwrite);
      $output->writeLn('Entity in Datei: "'.$file.'" geschrieben');
    }
    
    if ($repo) {
      $repoFile = $builder->writeRepository(NULL, $overwrite);
      $output->writeLn('EntityRepository in Datei: "'.$repoFile.'" geschrieben');
    }
    
    return 0;
  }
}
?>