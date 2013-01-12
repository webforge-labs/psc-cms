<?php

namespace Psc\Form;

use Psc\Data\ArrayCollection;
use Psc\CMS\EntityMeta;
use Psc\CMS\EntityPropertyMeta;
use Psc\Doctrine\DCPackage;
use Psc\Code\Code;

/**
 * Validiert die FormularAusgaben einer \Psc\UI\DropBox2
 *
 * @todo DropBox2SynchronizerValidatorRule könnte den hydration schritt vereinfachen
 */
class DropBox2ValidatorRule implements ValidatorRule {
  
  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   * EntityMeta des Entities in der DropBox
   *
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  
  public function __construct(EntityMeta $entityMeta, DCPackage $dc) {
    $this->setDoctrinePackage($dc);
    $this->setEntityMeta($entityMeta);
  }
  
  /**
   * 
   * Wird EmptyDataException bei validate() geworfen und ist der Validator mit optional aufgerufen worden, wird keine ValidatorException geworfen
   * @return $data
   */
  public function validate($data = NULL) {
    if ($data === NULL || $data === array()) throw EmptyDataException::factory(new \Psc\Data\ArrayCollection());
    if (!is_array($data)) throw new \InvalidArgumentException('Parameter ist als Array erwartet. '.Code::varInfo($data).' wurde übergeben');
    
    $identifierCaster = $this->getIdentifierCaster($this->entityMeta->getIdentifier());
    
    $repository = $this->dc->getRepository($this->entityMeta->getClass());
    $entities = new ArrayCollection();
    foreach ($data as $key=>$identifierString) {
      $identifier = $identifierCaster($identifierString, $key);
      
      $entities->add(
        $repository->hydrate($identifier)
      );
    }
    
    return $entities;
  }
  
  protected function getIdentifierCaster(EntityPropertyMeta $identifierMeta) {
    if ($identifierMeta->getType() instanceof \Psc\Data\Type\IntegerType) {
      $idCaster = function ($idString, $debugKey) {
        $id = \Psc\Code\Code::castId($idString, FALSE);
        if ($id === FALSE) {
          throw new ValidatorRuleException(sprintf("Der Wert '%s' des Schlüssels %s kann nicht zu einer numerischen Id gecastet werden",
                                      $idString, $debugKey));
        }
        return $id;
      };
      
    } elseif ($identifierMeta->getType() instanceof \Psc\Data\Type\StringType) {
      $idCaster = function ($idString, $debugKey) {
        if (!is_string($idString))
          throw new ValidatorRuleException(sprintf("Der Type '%s' des Schlüssels %s muss ein String sein",
                                      gettype($idString), $debugKey));
        
        if (mb_strlen($idString) == 0) {
          throw new ValidatorRuleException(sprintf("Der Wert '%s' des Schlüssels %s kann nicht zu einem Identifier gecastet werden",
                                      $idString, $debugKey));
          
        }
        
        return $idString;
      };
    } else {
      throw new \Psc\Code\NotImplementedException(sprintf('IdCaster für Type %s ist nicht implementiert', $identifierMeta->getType()));
    }
    
    return $idCaster;
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
}
?>