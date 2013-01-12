<?php

namespace Psc\Doctrine;

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\FixtureInterface as DCFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\ORM\EntityManager;

class FixturesManager extends \Psc\System\LoggerObject {
  
  protected $em;
  protected $executor;
  protected $loader;
  protected $purger;
  
  public function __construct(EntityManager $em) {
    $this->em = $em;
    $this->setLogger(new \Psc\System\BufferLogger);
  }
  
  public function add(DCFixture $fixture) {
    $this->getLoader()->addFixture($fixture);
    return $this;
  }
  
  public function execute() {
    return $this->getExecutor()->execute($this->getLoader()->getFixtures());
  }
  
  public function getLoader() {
    if (!isset($this->loader)) {
      $this->loader = new Loader();
    }
    return $this->loader;
  }
  
  public function getExecutor() {
    if (!isset($this->executor)) {
      $this->executor = new ORMExecutor($this->em, $this->getPurger());
      $that = $this;
      $this->executor->setLogger(function ($msg) use ($that) {
        $that->log($msg);
      });
    }
    
    return $this->executor;
  }
  
  public function getPurger() {
    if (!isset($this->purger)) {
      $this->purger = new ORMPurger($this->em);
      $this->purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
    }
    return $this->purger;
  }
}
?>