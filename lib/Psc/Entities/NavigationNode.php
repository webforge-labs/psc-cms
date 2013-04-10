<?php

namespace Psc\Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\NavigationNodeRepository")
 * @ORM\Table(name="navigation_nodes")
 * @ORM\HasLifecycleCallbacks
 */
class NavigationNode extends CompiledNavigationNode {

  public function __construct(Array $i18nTitle) {
    parent::__construct();
    $this->setI18nTitle($i18nTitle);
  }

  /**
   * @ORM\PrePersist
   * @ORM\PreUpdate
   */
  public function onPrePersist() {
    parent::onPrePersist();
  }

  protected function generateSlug($title) {
    // dumb
    return preg_replace('/\-+/', '-', preg_replace('/[^a-z0-9-]/', "-", mb_strtolower($title)));
  }

  public function getContextLabel($context = 'default') {
    if ($context === self::CONTEXT_DEFAULT) {
      return parent::getContextLabel();
    }
    
    return parent::getContextLabel();
  }
  
  public function getEntityName() {
    return 'Psc\Entities\NavigationNode';
  }

  public function __toString() {
    return 'navigation-node: '.current($this->getI18nTitle());
  }
}
?>