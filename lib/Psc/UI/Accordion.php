<?php

namespace Psc\UI;

use Psc\JS\Helper AS JSHelper;
use SimpleObject;
use Psc\Code\Code;
use Psc\A;

/**
 *
 * option.containerTag
 *   der Name des Tag welches die Steuerungs-klasse bekommt und ganz außen rum ist (div.accordion)
 * option.headlineTag
 *   der Name des Tag welches die Überschrift der Zeile ist (h3)
 * option.contentTag
 *   der Name des Tags welches den Content umschließt (div)
 * options
 *   array die Optionen die ans JS weitergegeben werden wenn option.js true ist
 * option.js
 *   wenn true (default), wird hinter das Objekt ein <script> - Tag mit dem JQuery-UI Aufruf gehängt. Der Selector ist $this->cssClass
 * 
 * 
 */
class Accordion extends OptionsObject {
  
  const END = A::END;
  const START = 0;
  
  protected $html;
  
  protected $cssClass;
  
  protected $indent = 4;
  
  public function __construct($jsOptions = array()) {
    parent::__construct();
    
    $this->setDefaultOptions(array(
      'containerTag'=>'div',
      'headlineTag'=>'h3',
      'contentTag'=>'div',

      // das sind die js options
      'options'=>      
          array_merge(
            array(
            'collapsible'=>true,
            ),
            $jsOptions
          )
       )
    );
    
    
    $this->html = new HTMLTag($this->getOption('containerTag','div'), array());
    $this->html->indent($this->indent);
    $this->html
      ->addClass($this->getOption('css.namespace').'accordion')
      ->setOption('br.openTag', TRUE)
    ;
  }
  
  public static function create(Array $jsOptions = array()) {
    return new static($jsOptions);
  }
  
  /**
   * Setzt die Optionen die an den eigene Javascript Aufruf übergeben werden
   *
   * option.js muss TRUE sein, damit das eigene Javascript angehängt wird
   */
  public function setJSOptions(Array $jsOptions = array()) {
    $this->setOption('options', $jsOptions);
    return $this;
  }
  
  public function setOption($name,$value = TRUE) {
    if (!in_array($name, array('js','containerTag','headlineTag','contentTag','options'))) {
      $this->options['options'][$name] = $value;
    } else {
      parent::setOption($name,$value);
    }
    return $this;
  }

  public function addRow($headline, $content, $index = self::END, $class = NULL) {
    if ($index === self::END) {
      $indexHeadline = $indexContent = self::END;
    } else {
                          // wenn bei 0 eingefügt werden soll ist head = 0 und div = 1
      $indexHeadline = $index*2; // wenn bei 2 eingefügt werden soll ist head = 4 (weil div+head 0 und div+head1 davor sind) und div = 5
      $indexContent = $indexHeadline+1;
    }
    
    \Psc\A::insert($this->html->content, $head = new HTMLTag($this->getOption('headlineTag'), $headline, array('class'=>$class)), $indexHeadline);
    \Psc\A::insert($this->html->content, $div = new HTMLTag($this->getOption('contentTag'), $content, array('class'=>$class)), $indexContent);
    $head->addClass('ui-accordion-header');
    
    $div->setOption('br.openTag',TRUE)->setOption('tag.indent', $this->indent+2);
  }
  
  public function addSection($headline, Array $content, $index = self::END, $openAllIcon = FALSE) {
    $class = \Psc\HTML\HTML::string2class($headline);
    
    $this->addRow(
                  '<a href="#">'.
                     HTML::esc($headline).
                   '</a>'.
                   ($openAllIcon ? '<span class="ui-icon open-all-list psc-cms-ui-icon-secondary ui-icon-newwin" title="Alle öffnen"></span>' : ''),
                   
                   $content,
                   $index,
                   $class
                  );
    return $this;
  }
  
  /**
   * Fügt Inhalt einer Section hinzu
   */
  public function appendSection($index, $snippet) {
    $index = $index*2+1;
    
    $this->html->content[$index]->content[] = $snippet;
    //throw new \Psc\Exception('weiss nicht wie ich an '.Code::varInfo($this->html->content[$index]).' appenden soll');
    return $this;
  }
  
  public function prependSection($index, $snippet) {
    $index = $index*2+1;
    
    array_unshift($this->html->content[$index]->content, $snippet);
    return $this;
  }
  
  public function setCSSClass($class) {
    if (isset($this->cssClass)) $this->html->removeClass($this->cssClass);
    $this->cssClass = $class;
    $this->html->addClass($this->cssClass);
  }
  
  public function html() {
    if ($this->getOption('js',TRUE)) {
      $accordionOptions = $this->getOption('options');
  
      jQuery::widget($this->html, 'accordion', $accordionOptions);
    }
    
    return $this->html;
  }
  
  /**
   * Ist immer save zu benutzen
   */
  public function addClass($class) {
    $this->getHTML()->addClass($class);
    return $this;
  }
  
  /**
   * Ist immer save zu benutzen
   */
  public function removeClass($class) {
    $this->getHTML()->removeClass($class);
    return $this;
  }
  
  /**
   * @return Tag
   */
  public function getHTML() {
    return $this->html;
  }
  
  public function __toString() {
    return (string) $this->html();
  }
}