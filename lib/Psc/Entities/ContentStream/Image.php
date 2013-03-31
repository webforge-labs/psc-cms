<?php

namespace Psc\Entities\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\ContentStream\ImageRepository")
 * @ORM\Table(name="cs_images")
 */
class Image extends CompiledImage {
  
  public function getContextLabel($context = 'default') {
    /*
    if ($context === self::CONTEXT_DEFAULT) {
      return parent::getContextLabel($context);
    }
    */
    return parent::getContextLabel($context);
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\Image';
  }
}
?>