<?php

namespace Psc\Entities\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\ContentStream\ContentStreamWrapperRepository")
 * @ORM\Table(name="content_stream_wrappers")
 */
class ContentStreamWrapper extends CompiledContentStreamWrapper {
  
  public function getContextLabel($context = 'default') {
    /*
    if ($context === self::CONTEXT_DEFAULT) {
      return parent::getContextLabel($context);
    }
    */
    return parent::getContextLabel($context);
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\ContentStreamWrapper';
  }
}
?>