<?php

namespace Psc\CMS\Roles;

use Psc\TPL\ContentStream\Collection as CollectionHelper;

/**
 * (at)ORM\HasLifecycleCallbacks
 */
abstract class PageEntity extends \Psc\CMS\AbstractEntity implements Page, WebsiteTemplateDisplayable {
  
  /**
  */
  public function updateTimestamps() {
    if (!isset($this->created)) {
      $this->created = \Psc\DateTime\DateTime::now();
    }
    $this->modified = \Psc\DateTime\DateTime::now();
    
    return $this;
  }

  /**
   * add this to the parent class with:
   * (at)ORM\PrePersist
   * (at)ORM\PreUpdate
   * 
   * add this to class level!
   * (at)ORM\HasLifecycleCallbacks
   */
  public function onPrePersist() {
    $this->updateTimestamps();

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
  
  /**
   * @return CoMun\Entities\ContentStream[]
   */
  public function getContentStreamsByLocale($revision = 'default') {
    return \Psc\Doctrine\Helper::reindex($this->getContentStreamsByRevision($revision), 'locale');
  }
  
  /**
   * @return CoMun\Entities\ContentStream
   */
  public function getContentStreamByLocale($locale, $revision = 'default') {
    $cs = $this->getContentStreamsByLocale($revision);
    return $cs[$locale];
  }

  /**
   * @return array
   */
  public function getContentStreamsByRevision($revision = 'default') {
    return array_filter($this->contentStreams->toArray(), function ($cs) use ($revision) {
      return $cs->getRevision() === $revision;
    });
  }


  /**
   * @return NavigationNode|NULL
   */
  public function getPrimaryNavigationNode() {
    return count($this->navigationNodes) > 0 ? $this->navigationNodes->get(0) : NULL;
  }
  
  public function getContextLabel($context = self::CONTEXT_DEFAULT) {
    if ($context === self::CONTEXT_GRID) {
      return $this->slug;
    }
    
    return $this->slug;
  }

  /**
   * @return bool
   */
  public function isActive() {
    return $this->active;
  }
}
?>