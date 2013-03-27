<?php

namespace Psc\TPL\ContentStream;

use Traversable;
use Psc\Data\ArrayCollection;
use RuntimeException;

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

  public function one() {
    if (($cnt = count($streams = $this->getFiltered())) === 1) {
      return current($streams);
    } else {
      throw new RuntimeException(
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

    return $streams;
  }

  /**
   * @return string
   */
  protected function debugFilters() {
    if (!isset($this->typeFilter) && !isset($this->localeFilter)) {
      return '(none)';
    }

    $d = '';

    if (isset($this->typeFilter)) {
      $d .= sprintf("type: '%s'", $this->typeFilter);
    }

    if (isset($this->localeFilter)) {
      $d .= sprintf("locale: '%s'", $this->localeFilter);
    }

    return $d;
  }
}
