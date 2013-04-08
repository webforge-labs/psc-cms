<?php

namespace Psc\CMS\Roles;

use Psc\CMS\AbstractEntity;
use Webforge\CMS\Navigation\Node as WebforgeNode;
use Psc\TPL\ContentStream\ContextLoadable;
use Psc\TPL\ContentStream\Context;
use Psc\TPL\ContentStream\Collection as CollectionHelper;

/**
 * (at)ORM\HasLifecycleCallbacks
 */
abstract class NavigationNodeEntity extends AbstractEntity implements WebforgeNode, ContextLoadable {

  public static function loadWithContentStreamContext($identifier, Context $context) {
    $identifier = (int) $identifier;
    if ($identifier > 0) {
      return $context->getNavigationController()->getEntity($identifier);
    }
  }

  /**
   * @return string
   */
  public function getNodeHTML() {
    return '<a>'.$this->getTitle('de').'</a>';
  }
  
  /**
   * @return bool
   */
  public function equalsNode(WebforgeNode $other = NULL) {
    if (!isset($other)) return FALSE;
    
    if ($this->isNew() || $other->isNew()) {
      return spl_object_hash($this) === spl_object_hash($other);
    }
    
    return $this->equals($other);
  }

  /**
   * (at)ORM\PrePersist
   * (at)ORM\PreUpdate
   */
  public function onPrePersist() {
    $this->generateSlugs();

    if (!isset($this->created)) {
      $this->created = \Psc\DateTime\DateTime::now();
    }
    $this->updated = \Psc\DateTime\DateTime::now();

    return $this;
  }

  /**
   * Returns a slug for the title
   * 
   * @return string
   */
  abstract protected function generateSlug($title);


  public function generateSlugs() {
    $titles = $this->getI18nTitle();
    
    foreach ($titles as $locale => $title) {
      $this->setSlug($this->generateSlug($title), $locale);
    }
    
    return $this;
  }

  /**
   * Fluid interface for getting specific ContentStream(s)
   * 
   * $page->getContentStream()
   *     ->locale('de')
   *     ->type('sidebar-content')
   *     ->one()
   *
   * @return Psc\TPL\ContentStream\Collection
   */
  public function getContentStream() {
    return new CollectionHelper($this->contentStreams->toArray());
  }
  
  public function getContextLabel($context = self::CONTEXT_DEFAULT) {
    return $this->getTitle('de');
  }  

  public function __toString() {
    return $this->getTitle('de');
  }
  
}
?>