<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    
    Psc\PSC
  ;

use Psc\Code\Generate\GClass;
use Psc\Code\Generate\ClassWriter;
use Psc\Code\Generate\ClassBuilder;

class CreateControllerCommand extends Command {

  protected function configure() {
    $this
      ->setName('cms:create-controller')
      ->setDescription(
        'Erstellt ein neuen Controller für ein Entity'
      )
      ->setDefinition(array(
        new InputArgument(
          'name', InputArgument::REQUIRED,
          'Der Name des Entites in CamelCase (erster Buchstabe Groß).'
        ),
        new InputOption(
          'overwrite','w',InputOption::VALUE_NONE,
          'Ist dies gesetzt werden bestehende Dateien überschrieben'
        )
      ))
      ->setHelp(
          $this->getName().' MyNewEntity'."\n".
          'Erstellt in project\Controllers\MyNewEntityController eine Instanz von einem EntityController'.PHP_EOL
      );
    }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $overwrite = $input->getOption('overwrite') ? ClassWriter::OVERWRITE : NULL;
    $controllerName = $input->getArgument('name');
    $entityClass = $this->getDoctrineModule()->getEntityName($controllerName);

    $gClass = new GClass();
    $gClass->setClassName($controllerName.'Controller');
    $gClass->setNamespace($this->getProject()->getNamespace().'\\Controllers'); // siehe auch EntityService
    $gClass->setParentClass(new GClass('Psc\CMS\Controller\AbstractEntityController'));
    
    $classBuilder = new ClassBuilder($gClass);
    $classBuilder->createProtectedMethod('setUp', array(), array(
      "parent::setUp();"
    ));
    
    $classBuilder->createMethod('getEntityName', array(), array(
      sprintf("return '%s';", $entityClass)
    ));

    $imports = array(new GClass($entityClass));
    
    $classWriter = new ClassWriter();
    $classWriter->setClass($gClass);
    $classWriter->write($file = $this->getProject()->getClassFile($gClass), $imports, $overwrite);

    $output->writeLn('Controller in Datei: "'.$file.'" geschrieben');
    return 0;
  }
}
?>