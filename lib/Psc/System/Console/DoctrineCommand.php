<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Psc\PSC,
    Psc\Doctrine\Helper as DoctrineHelper
  ;
  

class DoctrineCommand extends \Psc\System\Console\Command {
  
  protected $dc, $em, $module;
  
  protected $transactional = FALSE;

  protected function configure() {
    $this->addOption(
      'con', '', InputOption::VALUE_REQUIRED,
      'Name der Connection',
      'tests'
    );
    
    $this->addOption(
      'dry-run'
    );
  }
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $con = $input->getOption('con');
    $output->writeln('<comment>**DoctrineCommand: working in: '.$con.'</comment>');
    
    $this->module = $this->getDoctrineModule();
    $this->dc = new \Psc\Doctrine\DCPackage($this->module, $this->module->getEntityManager($con));
    $this->em = $this->dc->getEntityManager();
    
    if ($this->transactional) {
      $this->em->getConnection()->beginTransaction();
    }
    
    try {
      $ret = parent::execute($input, $output);

      if ($ret <= 0) {
        DoctrineHelper::enableSQLLogging('echo', $this->em);
        if ($input->getOption('dry-run')) {
          $this->warn('DryRun: nothing is flushed');
        } else {
          $this->em->flush();
        }
        
        if ($this->transactional) $this->em->getConnection()->commit();
      } else {
        if ($this->transactional) $this->em->getConnection()->rollback();
      }
    
    } catch(\Exception $e) {
      if ($this->transactional) {
        $this->em->getConnection()->rollback();
      }
      $this->em->close();
      throw $e;
    }
    
    return $ret;
  }
  
  public function hydrate($entity, $data) {
    if (is_array($data) && !\Webforge\Common\ArrayUtil::isNumeric($data)) // numeric bedeutet composite key (z.b. OID)
      return $this->getRepository($entity)->hydrateBy($data);
    else
      return $this->getRepository($entity)->hydrate($data);
  }

  /**
   * @return EntityRepository
   */
  public function getRepository($name) {
    return $this->em->getRepository($this->getEntityName($name));
  }
  
  /**
   * @return string
   */
  public function getEntityName($shortName) {
    return $this->module->getEntityName($shortName);
  }
}
