<?php

namespace Psc\CMS;

use Psc\Code\Code;
use Closure;

/**
 * 
 */
class AssociationList extends \Psc\SimpleObject {
  
  /**
   * @var string
   */
  protected $propertyName;
  
  /**
   * @var string
   */
  protected $format;
  
  /**
   * @var Closure
   */
  protected $withLabel;
  
  /**
   * @var bool
   */
  protected $withButton;
  
  /**
   * @var string
   */
  protected $joinedWith;

  /**
   * @var Closure
   */
  protected $sortBy;
  
  /**
   * @var string
   */
  protected $andJoiner;
  
  /**
   * @var Traversable
   */
  protected $entities = NULL;
  
  /**
   * @var string
   */
  protected $emptyText;
  
  /**
   * @var integer
   */
  protected $limit = FALSE;
  
  /**
   * @var string
   */
  protected $limitMessage;
  
  public function __construct($propertyName, $format, $emptyText = NULL) {
    $this->setPropertyName($propertyName);
    $this->setFormat($format);
    
    $this->emptyText = $emptyText ?: sprintf("Keine Verknüpfungen für '%s' vorhanden.", $propertyName);
  }
  
  /**
   * @param Closure $sorter function ($collection)
   */
  public function sortBy(Closure $sorter) {
    $this->sortBy = $sorter;
    return $this;
  }
  
  /**
   * @return Closure
   */
  public function getSortBy() {
    return $this->sortBy;
  }
  
  /**
   * @param string $propertyName
   */
  public function setPropertyName($propertyName) {
    $this->propertyName = $propertyName;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getPropertyName() {
    return $this->propertyName;
  }
  
  /**
   * @param string $format
   */
  public function setFormat($format) {
    $this->format = $format;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getFormat() {
    return $this->format;
  }
  
  /**
   * @param string $label der Name des Properties des Labels
   * @param Closure $label eine Closure mit erstem Parameter für das Objekt der AssociationList
   */
  public function withLabel($labelProperty) {
    if( is_string($labelProperty)) {
      $labelc = Code::castGetter($labelProperty);
    } elseif ($labelProperty instanceof Closure) {
      $labelc = $labelProperty;
    } else {
      throw new \InvalidArgumentException('withLabel parameter 1 kann nur Closure oder PropertyName sein');
    }
    $this->withLabel = $labelc;
    return $this;
  }
  
  public function withButton() {
    $this->withButton = TRUE;
    return $this;
  }
  
  public function getWithButton() {
    return $this->withButton;
  }
  
  /**
   * @return Closure
   */
  public function getWithLabel() {
    return $this->withLabel;
  }
  
  /**
   * @param string $joinedWith
   */
  public function joinedWith($joinedWith) {
    $this->joinedWith = $joinedWith;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getJoinedWith() {
    return $this->joinedWith;
  }
  
  /**
   * @param string $andJoiner
   */
  public function withAndJoiner($andJoiner) {
    $this->andJoiner = $andJoiner;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getAndJoiner() {
    return $this->andJoiner;
  }
  
  /**
   * @param Traversable $entities
   */
  public function setEntities($entities) {
    $this->entities = $entities;
    return $this;
  }
  
  /**
   * @return Traversable
   */
  public function getEntities() {
    return $this->entities;
  }
  
  public function hasEntities() {
    return $this->entities !== NULL;
  }
  
  /**
   * @param string $emptyText
   */
  public function setEmptyText($emptyText) {
    $this->emptyText = $emptyText;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getEmptyText() {
    return $this->emptyText;
  }
  
  /**
   * @param integer $limit
   * @param string $message kann %d enthalten für die Anzahl der Verknüfungen und wird angezeigt, wenn das limit überschritten wird
   */
  public function setLimit($limit, $message) {
    $this->limit = $limit;
    $this->limitMessage = $message;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getLimit() {
    return $this->limit;
  }
  
  public function hasLimit() {
    return $this->limit !== FALSE;
  }
  
  /**
   * @param string $limitMessage
   */
  public function setLimitMessage($limitMessage) {
    $this->limitMessage = $limitMessage;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getLimitMessage() {
    return $this->limitMessage;
  }
}
