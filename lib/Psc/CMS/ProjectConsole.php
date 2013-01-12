<?php

namespace Psc\CMS;

use Psc\PSC;

class ProjectConsole extends \Psc\System\Console\Console {
  
  protected $project;
  
  protected $doctrine;
  
  public function __construct(Project $project = NULL, \Psc\Doctrine\Module $doctrine = NULL) {
    parent::__construct();
    $this->project = $project ?: PSC::getProject();
    $this->name = $this->project->getName().' Developer Interface';
    $this->version = (string) PSC::getVersion($this->project);
    $this->doctrine = $doctrine;
    $this->setUp();
  }
  
  protected function setUp() {
    $this->cli->getHelperSet()
      ->set(new \Psc\System\Console\ProjectHelper($this->project), 'project');

    if (isset($this->doctrine)) {
      $em = $this->doctrine->getEntityManager();
      $this->cli->getHelperSet()
        ->set(new \Psc\System\Console\DoctrinePackageHelper(new \Psc\Doctrine\DCPackage($this->doctrine, $em)), 'dc');
      $this->cli->getHelperSet()
        ->set(new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection(), 'db'));
      $this->cli->getHelperSet()
        ->set(new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em), 'em');
    }
  }
  
  public function addCommands() {
    $this->cli->addCommands(array_merge(array(
      new \Psc\System\Console\RunTestCommand(),
      new \Psc\System\Console\CreateClassCommand(),
      new \Psc\System\Console\CreateTestCommand(),
      new \Psc\System\Console\CreateControllerCommand(),
      new \Psc\System\Console\WriteHtaccessCommand(),
      
      new \Psc\System\Console\SismoBuildCommand(),
      new \Psc\System\Console\AddClassPropertyCommand(),
      
      new \Psc\System\Console\CreateUserCommand(),
      new \Psc\System\Console\ORMSchemaCommand(),
      new \Psc\System\Console\ORMCreateEntityCommand(),
      
      new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
      new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand(),
      new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand(),
      new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand(),
      new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand(),
    ), $this->includeCommands()));
  }
}
?>