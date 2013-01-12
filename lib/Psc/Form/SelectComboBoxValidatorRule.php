<?php

namespace Psc\Form;

use Psc\CMS\EntityMeta;
use Psc\Doctrine\DCPackage;

/**
 * Validiert die Form Ausgaben einer ComboBox mit selectMode = true (aka SelectComboBox)
 * 
 * von der SelectComboBox (aka ComboBox mit selectMode = true) kommt nur der Identifier des Entities zurück
 * wir müssen also vorher wissen, welches entity validiert werden soll
 * umd das zu validieren / hydrieren brauchen wir ein dc package
 *
 * Achtung: wird auch von UI\Component\SingleImage benutzt
 */
class SelectComboBoxValidatorRule implements ValidatorRule {
  
  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  public function __construct($entityName, DCPackage $dc) {
    $this->setDoctrinePackage($dc);
    $this->entityMeta = $dc->getEntityMeta($entityName);
  }
  
  /**
   * 
   * Achtung! nicht bool zurückgeben, stattdessen irgendeine Exception schmeissen
   * 
   * Wird EmptyDataException bei validate() geworfen und ist der Validator mit optional aufgerufen worden, wird keine ValidatorException geworfen
   * 
   * @return $data
   */
  public function validate($data) {
    if ($data === NULL) throw EmptyDataException::factory(NULL);
    
    $repository = $this->dc->getRepository($this->entityMeta->getClass());
    
    try {
      return $repository->hydrate($data);
    
    } catch (\Psc\Doctrine\EntityNotFoundException $e) {
      throw EmptyDataException::factory(NULL, $e);
    }
  }
  
  /**
   * @param Psc\CMS\EntityMeta $entityMeta
   */
  public function setEntityMeta(EntityMeta $entityMeta) {
    $this->entityMeta = $entityMeta;
    return $this;
  }
  
  /**
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta() {
    return $this->entityMeta;
  }
  
  /**
   * @param Psc\Doctrine\DCPackage $dc
   */
  public function setDoctrinePackage(DCPackage $dc) {
    $this->dc = $dc;
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage() {
    return $this->dc;
  }
}
?>