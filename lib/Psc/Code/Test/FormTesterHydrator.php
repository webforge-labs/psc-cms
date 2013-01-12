<?php

namespace Psc\Code\Test;

use Psc\Doctrine\Hydrator;
use Doctrine\ORM\EntityManager;

class FormTesterHydrator extends \Psc\Object {
  
  /**
   * @var Psc\Doctrine\Hydrator[] Schlüssel sind die Namen der Properties
   */
  protected $dcHydrators;
  
  
  /**
   * @var Psc\Doctrine\EntityManager
   */
  protected $em;
  
  /**
   * @var Psc\Code\Test\FormTesterData
   */
  protected $data;
  
  public function __construct(FormTesterData $data, EntityManager $em) {
    $this->data = $data;
    $this->dcHydrators = array();
    $this->em = $em;
  }
  
  /**
   * Hydriert eine Collection die in expectedData als Array angegeben ist
   *
   * Setzt die Daten in $this->data->expectedRow
   * @param string $idname der Name des Feldes dessen werte in $data->expected->$property im array stehen
   * @chainable
   */
  public function collection($property, $idname) {
    // get
    $identifiers = $this->data->getExpectedValue($property); // macht merge für values
    
    if (!is_array($identifiers)) {
      throw new FormTesterException("Wert in FormTesterData::\$expectedRow für Property '".$property."' muss ein array sein!");
    }

    // hydrate
    $dcHydrator = $this->getDoctrineHydratorFor($property);
    $collection = $dcHydrator->byList($identifiers, $idname, $checkCardinality = TRUE);
    
    // set
    $this->data->getExpectedRow()->setData($property, $collection);
    
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\Hydrator
   */
  public function getDoctrineHydratorFor($property) {
    if (!array_key_exists($property, $this->dcHydrators)) {
      $e = new MissingPropertyHydratorException(sprintf("Für das Property: '%s' wurde kein Hydrator gesetzt. (setDoctrineHydratorFor()", $property));
      $e->property = $property;
      throw $e;
    }
    
    return $this->dcHydrators[$property];
  }
  
  /**
   * Setzt den DoctrineHydrator für ein bestimmtes Property
   *
   * z. B. für Tags
   *
   * $this->setDoctrineHydratorFor('tags', new \Psc\Doctrine\Hydrator('Entities\SoundTag'));
   * der EntityManager im Hydrator wird automatisch auf den von uns gesetzt
   */
  public function setDoctrineHydratorFor($property, \Psc\Doctrine\Hydrator $hydrator) {
    $hydrator->setEntityManager($this->em);
    $this->dcHydrators[$property] = $hydrator;
    return $this;
  }
}
?>