<?php

namespace Psc\UI;

use \Psc\UI\Form as f;

class Group extends HTMLTag {
  
  protected $flags;
  
  const CONTAINING_BLOCK = 0x000004;
  const COLLAPSIBLE      = f::GROUP_COLLAPSIBLE;
  
  /**
   * Eine Gruppe von Element mit einer Überschrift
   *
   * der Inhalt ist eingerückt und es befindet sich ein Rahmen herum
   * @param mixed $legend der inhalt der Überschrift
   * @param mixed $content der Inhalt des Containers
   */
  public function __construct($legend, $content = NULL, $flags = 0x000000) {
    parent::__construct('fieldset', new \stdClass);
    $this
      ->addClass('ui-corner-all')
      ->addClass('ui-widget-content')
      ->addClass('\Psc\group')
    ;
    $this->contentTemplate = "%legend%\n  %div%";
    $this->content->legend = HTML::tag('legend',$legend);
    $this->content->div = HTML::tag('div',$content,array('class'=>'content'));

    $this->flags = $flags;
    if ($this->flags & self::COLLAPSIBLE) {
      $this->setCollapsible();
    }
    
    if ($this->flags & self::CONTAINING_BLOCK) {
      $this->addClass('containing-block');
    }
  }
  
  public static function create($legend, $content = NULL, $flags = 0x000000) {
    return new static($legend, $content, $flags);
  }
  
  public function addContent($content) {
    $this->content->div->content .= $content;
    return $this;
  }
  
  public function getContentTag() {
    return $this->content->div;
  }
  
  public function setLegend($legend) {
    $this->content->legend->content = $legend;
    return $this;
  }
  
  public function collapse() {
    $this->setCollapsible(TRUE);
    $this->content->div->setStyle('display','none');
    return $this;
  }

  public function setCollapsible($mode = TRUE) {
    if ($mode) {
      $this->flags |= self::COLLAPSIBLE; // just for the record
      $this->content->legend->addClass('collapsible');
    } else {
      $this->flags &= ~self::COLLAPSIBLE; // just for the record
      $this->content->legend->removeClass('collapsible');
    }
    return $this;
  }
}
?>