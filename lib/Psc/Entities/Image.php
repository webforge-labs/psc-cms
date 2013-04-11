<?php

namespace Psc\Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\ImageRepository")
 * @ORM\Table(name="images", uniqueConstraints={@ORM\UniqueConstraint(name="images_hash", columns={"hash"})})
 * @ORM\HasLifecycleCallbacks
 */
class Image extends CompiledImage {

  public function getEntityName() {
    return 'Psc\Entities\Image';
  }

  /**
   * @ORM\PostRemove
   */
  public function triggerRemoved() {
    return parent::triggerRemoved();
  }
}
?>