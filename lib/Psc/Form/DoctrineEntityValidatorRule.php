<?php

namespace Psc\Form;

use \Psc\Doctrine\Helper as DoctrineHelper;

class DoctrineEntityValidatorRule implements ValidatorRule {
  
  protected $em;
  protected $entityName;
  protected $idName;
  
  public function __construct($entityName, $idName = 'id', $em = NULL) {
    if (!isset($em)) $em = DoctrineHelper::em();
    
    $this->em = $em;
    $this->idName = $idName;
    $this->entityName = DoctrineHelper::getEntityName($entityName);
  }
  
  /**
   * @return \Psc\Doctrine\Object
   */
  public function validate($data) {
    
    $idRule = new IdValidatorRule();
    $id = $idRule->validate($data); // schmeisst empty data exception wenn necessary
    
    $object = $this->em->getRepository($this->entityName)->findOneBy(array($this->idName=>$id));
    
    if ($object instanceof $this->entityName) {
      return $object;
    }
    
    throw new \Psc\Exception('Entity konnte nicht gefunden werden');
  }
}

?>