<?php

namespace Psc\UI\jqx;

use Psc\UI\HTML;

/**
 * 
 */
class Tab extends \Psc\HTML\Base {
  
  /**
   * Irgendwas HTML mäßiges
   * @var mixed
   */
  protected $head;
  
  /**
   * Irgendwas HTML mäßiges (kann leer sein)
   * @var mixed
   */
  protected $content;
  
  public function __construct($head, $content = NULL) {
    $this->setHead($head);
    $this->setContent($content);
  }
  
  public static function createWithTitle($title, $content = NULL) {
    return new static(HTML::esc($title), $content);
  }
  
  protected function doInit() {
    $this->html = $this->content;
  }
  
  public function htmlHead() {
    return $this->head;
    //return HTML::tag('div', $this->head);
  }
  
  /**
   * @param mixed $head
   */
  public function setHead($head) {
    $this->head = $head;
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function getHead() {
    return $this->head;
  }
  
  public function getContent() {
    return $this->content;
  }
  
  /**
   * @param mixed $content
   */
  public function setContent($content) {
    $this->content = $content;
    return $this;
  }
}
?>