<?php

namespace Psc\TPL\ContentStream;

abstract class ContentStreamEntity extends \Psc\CMS\AbstractEntity {
  
  /**
   * @param Psc\TPL\ContentStream\Entry $entry
   * @chainable
   */
  public function addEntry(Entry $entry) {
    if (!$this->entries->contains($entry)) {
      $this->entries->add($entry);
    }
    return $this;
  }

  /**
   * @param Psc\TPL\ContentStream\Entry $entry
   * @chainable
   */
  public function removeEntry(Entry $entry) {
    if ($this->entries->contains($entry)) {
      $this->entries->removeElement($entry);
    }
    return $this;
  }

  /**
   * @param Psc\TPL\ContentStream\Entry $entry
   * @return bool
   */
  public function hasEntry(Entry $entry) {
    return $this->entries->contains($entry);
  }
}
?>