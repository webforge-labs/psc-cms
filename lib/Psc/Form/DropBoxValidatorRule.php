<?php

namespace Psc\Form;

use \Psc\Code\Code,
    \Psc\Doctrine\Helper AS DoctrineHelper
;

class DropBoxValidatorRule implements ValidatorRule {
  
  protected $entityName;
  protected $idName;
  protected $em;
  
  public function __construct($entityName, $idName = 'id', $em = NULL) {
    if (!isset($em)) $em = DoctrineHelper::em();
    $this->em = $em;
    
    $this->idName = $idName;
    $this->entityName = DoctrineHelper::getEntityName($entityName);
  }
  
  public function validate($data) {
    if (empty($data)) {
      $e = new EmptyDataException();
      $e->setDefaultValue(array());
      throw $e;
    }
    $data = json_decode($data,true);
    
    if (count($data) == 0) {
      $e = new EmptyDataException();
      $e->setDefaultValue(array());
      throw $e;
    }
    
    $rep = $this->em->getRepository($this->entityName);
    $collection = $rep->findAllByIds($data, $this->idName); // überspringt dies fehlende Ids?
    
    // sortieren nach der Data List (da findAllByIds das nicht tut, und das ist auch gut so)
    $getter = \Psc\Code\Code::castGetter($this->idName);
    $keys = array_flip($data);
    usort($collection, function ($entityA, $entityB) use ($keys, $getter) {
      $keyA = $keys[$getter($entityA)];
      $keyB = $keys[$getter($entityB)];
      if ($keyA === $keyB) return 0;
      return $keyA > $keyB ? 1 : -1;
    });
    
    if (count($collection) == count($data)) {
      return $collection;
    } else {
      throw new \Psc\Exception('Unexpected: Es wurden: '.count($collection).' IDs hydriert, aber '.count($data).' IDs angegeben.');
    }
  }
}

?>