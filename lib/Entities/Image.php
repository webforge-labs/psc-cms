<?php

namespace Entities;

/**
 * Entity(repositoryClass="GREG\Entities\ImageRepository")
 * @Entity
 * @Table(name="images")
 * @HasLifecycleCallbacks
 */
class Image extends \Psc\Doctrine\Entities\BasicImage {

  public function getEntityName() {
    return 'Entities\Image';
  }

  /**
   * @PostRemove
   */
  public function triggerRemoved() {
    return parent::triggerRemoved();
  }
  
}

?>