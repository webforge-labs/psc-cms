<?php

namespace Psc\Doctrine\Mocks;

use Doctrine\DBAL\Connection;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Psc\Data\ArrayCollection;
use Psc\Code\Code;

/**
 * Eine Klasse die es erlaubt den EntityManager transparenter zu testen
 *
 * die Klasse verhält sich wie ein "logger", der z. B. sammelt welche Entities persisted wurden, oder ob ein Flush ausgeführt
 * wurde
 */
class EntityManager extends \Doctrine\ORM\EntityManager {
  
  protected $flushs = 0;

  protected $persisted;
  protected $removed;
  protected $merged; // ist als einzige etwas anders als die anderen loggt nur merged entities (wird nie removed)
  protected $detached;

  protected $delegate = FALSE;
  
  public function __construct(Connection $conn = NULL, Configuration $config = NULL, EventManager $eventManager = NULL) {
    $this->persisted = new ArrayCollection();
    $this->removed = new ArrayCollection();
    $this->merged = new ArrayCollection();
    $this->detached = new ArrayCollection();
    
    if (isset($conn))
      parent::__construct($conn, $config, $eventManager);
  }
  
  public function flush($entity = null) {
    $this->flushs++;
    
    if ($this->delegate) 
      return parent::flush($entity);
  }
  
  public function remove($entity) {
    // add to remove
    if (!$this->removed->contains($entity)) {
      $this->removed->add($entity);
    }

    // remove from persisted, detached
    if ($this->persisted->contains($entity)) {
      $this->persisted->removeElement($entity);
    }
    if ($this->detached->contains($entity)) {
      $this->detached->removeElement($entity);
    }
    
    if ($this->delegate) {
      return parent::remove($entity);
    }
  }
  
  public function persist($entity) {
    // add to persist
    if (!$this->persisted->contains($entity)) {
      $this->persisted->add($entity);
    }
    
    // remove from removed,detached,merged
    //if ($this->removed->contains($entity)) {
    //  $this->removed->removeElement($entity);
    //}
    //if ($this->detached->contains($entity)) {
    //  $this->detached->removeElement($entity);
    //}
    //
    if ($this->delegate) {
      return parent::persist($entity);
    }
  }

  /**
   * Der Parameter von merge wird nicht im EntityManager persisted
   */
  public function merge($entity) {
    // add to merged
    if (!$this->merged->contains($entity)) {
      $this->merged->add($entity);
    }
    
    // remove from removed,detached
    // nicht remove from persisted
    if ($this->removed->contains($entity)) {
      $this->removed->removeElement($entity);
    }
    if ($this->detached->contains($entity)) {
      $this->detached->removeElement($entity);
    }
    
    if ($this->delegate) {
      return parent::persist($entity);
    }
  }

  public function detach($entity) {
    // add to detached
    if (!$this->detached->contains($entity)) {
      $this->detached->add($entity);
    }
    
    // remove from all
    if ($this->persisted->contains($entity)) {
      $this->persisted->removeElement($entity);
    }
    if ($this->removed->contains($entity)) {
      $this->removed->removeElement($entity);
    }
    
    if ($this->delegate) {
      return parent::detach($entity);
    }
  }
  
  public function clear($entityName = null) {
    $delegate = $this->delegate;
    $this->delegate = FALSE;
    
    // remove all
    foreach ($this->persisted as $entity) {
      $this->detach($entity);
    }
    foreach ($this->removed as $entity) {
      $this->detach($entity);
    }
    $this->delegate = $delegate;

    // hier sollten alle leer sein
    if (count($this->persisted) > 0) {
      throw new \Psc\Exception('MockFehler: Persisted ist nicht leer');
    }
    if (count($this->removed) > 0) {
      throw new \Psc\Exception('MockFehler: Removed ist nicht leer');
    }
    
    $this->merged = new ArrayCollection();
    
    if ($this->delegate) {
      return parent::clear($entityName);
    }
  }
  
  public function close() {
    if ($this->delegate) {
      return parent::close();
    } else {
      $this->clear();
    }
  }
  
  
  public function getRepositoryMock(\Psc\Code\Test\Base $testCase, $entityName, Array $methods = NULL) {
    $realRepository = $this->getRepository($entityName);
    $class = Code::getClass($realRepository);
    
    return $testCase->getMock($class,
                              $methods,
                              array($this, $this->getClassMetadata($entityName))
                             );
  }
  
  /**
   * @param array $persisted
   * @chainable
   */
  public function setPersisted(array $persisted) {
    $this->persisted = new ArrayCollection($persisted);
    return $this;
  }

  /**
   * @return arrayCollection
   */
  public function getPersisted() {
    return $this->persisted;
  }

  

  /**
   * @param array $removed
   * @chainable
   */
  public function setRemoved(array $removed) {
    $this->removed = new ArrayCollection($removed);
    return $this;
  }

  /**
   * @return arrayCollection
   */
  public function getRemoved() {
    return $this->removed;
  }

  /**
   * @param array $detached
   * @chainable
   */
  public function setDetached(array $detached) {
    $this->detached = new ArrayCollection($detached);
    return $this;
  }

  /**
   * @return arrayCollection
   */
  public function getDetached() {
    return $this->detached;
  }

  /**
   * @param array $merged
   * @chainable
   */
  public function setMerged(array $merged) {
    $this->merged = new ArrayCollection($merged);
    return $this;
  }

  /**
   * @return arrayCollection
   */
  public function getMerged() {
    return $this->merged;
  }

  public function getMockCollection($name) {
    if ($name == 'persisted') {
      return $this->getPersisted();
    } elseif ($name === 'removed') {
      return $this->getRemoved();
    } elseif ($name === 'detached') {
      return $this->getDetached();
    } elseif ($name === 'merged') {
      return $this->getMerged();
    } else {
      throw new \Psc\Exception('Mock: unbekannte MockCollection: '.$name);
    }
  }
  
  public function getFlushs() {
    return $this->flushs;
  }
  
  public function setFlushs($f) {
    $this->flushs = max(0, (int) $f);
    return $this;
  }
}
?>