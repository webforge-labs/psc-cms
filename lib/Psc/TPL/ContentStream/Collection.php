<?php

namespace Psc\TPL\ContentStream;

use Traversable;
use Psc\Data\ArrayCollection;

class Collection {

  /**
   * @var array
   */
  protected $streams;

  /**
   * @var string
   */
  protected $typeFilter;

  /**
   * @var string
   */
  protected $localeFilter;

  /**
   * @var string
   */
  protected $revisionFilter;

  /**
   * @param Array $contentStreams
   */
  public function __construct(Array $contentStreams) {
    $this->streams = $contentStreams;
  }

  public function locale($locale) {
    $this->localeFilter = $locale;
    return $this;
  }

  public function type($typeName) {
    $this->typeFilter = $typeName;
    return $this;
  }

  public function revision($revisionName) {
    $this->revisionFilter = $revisionName;
    return $this;
  }

  public function one() {
    $cnt = count($streams = $this->getFiltered());
    if ($cnt === 1) {
      return current($streams);
    } elseif($cnt === 0) {
      throw new NoContentStreamsFoundException(
        sprintf('No ContentStreams found for filters: %s in one().',$this->debugFilters())
      );
    } else {
      throw new MultipleContentStreamsFoundException(
        sprintf('Multiple ContentStreams (%d) found for filters: %s. Use collection() to get multiple.', $cnt, $this->debugFilters())
      );
    }
  }

  /**
   * @return Psc\Data\ArrayCollection
   */
  public function collection() {
    return new ArrayCollection($this->getFiltered());
  }

  /**
   * @return array
   */
  protected function getFiltered() {
    $typeFilter = $this->typeFilter;
    $localeFilter = $this->localeFilter;
    $revisionFilter = $this->revisionFilter;
    
    $streams = $this->streams;

    if (isset($typeFilter)) {
      $streams = array_filter($streams, function (ContentStream $cs) use ($typeFilter) {
        return $cs->getType() === $typeFilter;
      });
    }

    if (isset($localeFilter)) {
      $streams = array_filter($streams, function (ContentStream $cs) use ($localeFilter) {
        return $cs->getLocale() === $localeFilter;
      });
    }

    if (isset($revisionFilter)) {
      $streams = array_filter($streams, function (ContentStream $cs) use ($revisionFilter) {
        return $cs->getRevision() === $revisionFilter;
      });
    }

    return $streams;
  }

  /**
   * @return string
   */
  protected function debugFilters() {
    if (!isset($this->typeFilter) && !isset($this->localeFilter) && !isset($this->revisionFilter)) {
      return '(none)';
    }

    $d = '';

    if (isset($this->typeFilter)) {
      $d .= sprintf("type: '%s' ", $this->typeFilter);
    }

    if (isset($this->localeFilter)) {
      $d .= sprintf("locale: '%s' ", $this->localeFilter);
    }

    if (isset($this->revisionFilter)) {
      $d .= sprintf("revision: '%s' ", $this->revisionFilter);
    }

    return $d;
  }
}
