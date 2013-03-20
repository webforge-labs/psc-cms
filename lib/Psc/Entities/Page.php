<?php

namespace Psc\Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\PageRepository")
 * @ORM\Table(name="pages")
 * @ORM\HasLifecycleCallbacks
 */
class Page extends CompiledPage {
  
  /**
   * @ORM\PrePersist
   * @ORM\PreUpdate
   */
  public function updateTimestamps() {
    return parent::updateTimestamps();
  }


  public function getContextLabel($context = 'default') {
    if ($context === self::CONTEXT_DEFAULT) {
      return parent::getContextLabel();
    }
    
    return parent::getContextLabel();
  }
  
  public function getEntityName() {
    return 'Psc\Entities\Page';
  }
}
?>