<?php

namespace Psc\Entities\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\ContentStream\LiRepository")
 * @ORM\Table(name="lis")
 */
class Li extends CompiledLi {
  
  public function getContextLabel($context = 'default') {
    /*
    if ($context === self::CONTEXT_DEFAULT) {
      return parent::getContextLabel($context);
    }
    */
    return parent::getContextLabel($context);
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\Li';
  }
}
?>