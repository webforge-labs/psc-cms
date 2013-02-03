<?php

namespace Psc\Doctrine;

use Doctrine\ORM\EntityManager;
use Psc\PSC;
use Psc\CMS\EntityMetaProvider;

class DCPackage extends \Psc\SimpleObject implements EntityMetaProvider {

  /**
   * @var Doctrine\ORM\EntiyManager
   */
  protected $em;
  
  /**
   * @var Psc\Doctrine\Module
   */
  protected $module;
  
  public function __construct(Module $module = NULL, EntityManager $em = NULL) {
    $this->setModule($module ?: PSC::getProject()->getModule('Doctrine'));
    $this->setEntityManager($em ?: $this->module->getEntityManager());
  }
  
  /**
   * @return Psc\Doctrine\EntityRepositoy
   */
  public function getRepository($entityName) {
    return $this->em->getRepository($entityName);
  }
  
  /**
   * @return string
   */
  public function expandEntityName($shortEntityName) {
    return $this->module->getEntityName($shortEntityName);
  }

  /**
   * @return Doctrine\ORM\Mapping\ClassMetadata
   */
  public function getClassMetadata($entityName) {
    return $this->em->getClassMetadata($entityName);
  }
  
  /**
   * @return Entity
   */
  public function hydrate($entityName, $identifier) {
    return $this->getRepository($entityName)->hydrate($identifier);
  }

  /**
   * @return Entity
   */
  public function hydrateBy($entityName, Array $criterias) {
    return $this->getRepository($entityName)->hydrateBy($criterias);
  }
  
  /**
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta($entityName) {
    return $this->module->getEntityMeta($entityName);
  }
  
  /**
   * @param Module $module
   * @chainable
   */
  public function setModule(Module $module) {
    $this->module = $module;
    return $this;
  }

  /**
   * @return Module
   */
  public function getModule() {
    return $this->module;
  }

  /**
   * @param Doctrine\ORM\EntityManager $em
   */
  public function setEntityManager(EntityManager $em) {
    $this->em = $em;
    return $this;
  }
  
  public function changeEntityManager($con) {
    $this->em = $this->module->getEntityManager($con);
    return $this;
  }
  
  /**
   * @return Doctrine\ORM\EntityManager
   */
  public function getEntityManager() {
    return $this->em;
  }

  /**
   * @return bool
   */
  public function hasActiveTransaction() {
    return $this->em->getConnection()->isTransactionActive();
  }

  public function beginTransaction() {
    return $this->em->getConnection()->beginTransaction();
  }
  
  public function commitTransaction() {
    return $this->em->getConnection()->commit();
  }
  
  public function rollbackTransaction() {
    return $this->em->getConnection()->rollback();
  }
}
?>