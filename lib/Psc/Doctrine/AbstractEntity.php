<?php

namespace Psc\Doctrine;

/**
 * @TODO Type-Meta erweiterungen für Psc\Doctrine\Entity
 */
abstract class AbstractEntity extends \Psc\Doctrine\BaseEntity implements \Psc\Doctrine\Entity, \Doctrine\Common\Comparable {
  
  /**
   * @see Psc\Doctrine\Entity
   * @inheritdoc
   */
  public function equals(Entity $otherEntity = NULL) {
    if ($otherEntity === NULL) return FALSE;
    if ($this->getEntityName() !== $otherEntity->getEntityName()) return FALSE;
    
    return $this->getIdentifier() === $otherEntity->getIdentifier();
  }

  /**
   * @return int 0|-1|1
   */
  public function compareTo($other) {
    if ($this->equals($other)) return 0;
    
    return $this->getIdentifier() > $other->getIdentifier() ? 1 : -1;
  }
  
  /**
   * Gibt den FQN des Entities zurück
   *
   * dies kann man zur Performance überschreiben
   * @return string
   */
  public function getEntityName() {
    return \Psc\Code\Code::getClass($this);
  }
  
  public function isNew() {
    return $this->getIdentifier() === NULL;
  }
  
  /**
   * @see Psc\Doctrine\Entity
   * @inheritdoc
   */
  public function getEntityLabel() {
    return sprintf('Psc\Doctrine\Entity<%s> [%s]',
                   $this->getEntityName(), $this->getIdentifier() //, $item->getTabsLabel()
                  );
  }
}
?>