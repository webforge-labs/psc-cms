<?php

namespace Entities;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="images")
 * @ORM\HasLifecycleCallbacks
 */
class Image extends \Psc\Doctrine\Entities\BasicImage {

  public function getEntityName() {
    return 'Entities\Image';
  }

  /**
   * @ORM\PostRemove
   */
  public function triggerRemoved() {
    return parent::triggerRemoved();
  }
}
?>