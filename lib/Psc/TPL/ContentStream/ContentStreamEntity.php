<?php

namespace Psc\TPL\ContentStream;

use RuntimeException;
use Doctrine\Common\Collections\Collection;

abstract class ContentStreamEntity extends \Psc\CMS\AbstractEntity {

  /**
   * @param Psc\TPL\ContentStream\Entry $entry
   * @chainable
   */
  public function addEntry(Entry $entry) {
    if (!$this->entries->contains($entry)) {
      $this->entries->add($entry);
      $entry->setSort($this->entries->indexOf($entry)+1);
    }
    return $this;
  }

  /**
   * @param Doctrine\Common\Collections\Collection<CoMun\Entities\ContentStream\Entry> $entries
   */
  public function setEntries(Collection $entries) {
    $this->entries = $entries;
    foreach ($this->entries as $key => $entry) {
      $entry->setSort($key+1);
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

  /**
   * Gibt alle Elemente nach dem angegeben Element im CS zurück
   *
   * gibt es keine Element danach wird ein leerer Array zurückgegeben
   * gibt es das Element nicht, wird eine Exception geschmissen (damit ist es einfacher zu kontrollieren, was man machen will)
   * das element wird nicht im Array zurückgegeben
   * 
   * @return array
   * @throws RuntimeException
   */
  public function findAfter(Entry $entry) {
    $pos = $this->entries->indexOf($entry);
    if ($pos === FALSE) {
      throw new RuntimeException('Das Element '.$entry.' ist nicht im ContentStream. findAfter() ist deshalb undefiniert');
    }
    
    return array_merge($this->entries->slice($pos+1));
  }
  
  public function getContextLabel($context = self::CONTEXT_DEFAULT) {
    if (isset($this->slug)) {
      $label = sprintf('Seiteninhalt '.$this->slug);
    } else {
      $label = sprintf('Seiteninhalt #%d', $this->getIdentifier());
    }
    
    if (isset($this->locale)) {
      $label .= ' '.$this->locale;
    }
    return $label;
  }
  
  /**
   * Gibt das erste Vorkommen der Klasse im Stream zurück
   *
   * gibt es kein Vorkommen wird NULL zurückgegeben
   * @param string type ohne Namespace davor z.b. downloadlist für Downloadlist
   */
  public function findFirst($type) {
    $class = $this->getTypeClass($type);
    foreach ($this->entries as $entry) {
      if ($entry instanceof $class) {
        return $entry;
      }
    }
  }
  
  abstract public function getTypeClass($typeName);
}
?>