<?php

namespace Psc\HTML;

use Psc\CSS\CSS;
use Psc\CSS\Manager AS CSSManager;
use Psc\JS\Manager AS JSManager;
use Psc\JS\JS;
use stdClass;

class Page extends \Psc\OptionsObject implements \Psc\HTML\HTMLInterface {
  
  /**
   * @var Tag<html>
   */
  protected $html;
  
  /**
   * Der XHTML DocType
   * @var string
   */
  protected $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';


  /**
   * Sprache des HTML Contents
   * @var string 2 stelliger Länder-Code
   */
  protected $language = 'de';

  /**
   * Content-Type
   * @var string nach ISO (welche?)
   */
  protected $contentType = 'text/html';


  /**
   * Das Content-Charset
   * @var string charset in ISO (welche?)
   */
  protected $charset = 'utf-8';
  
  /**
   * Der Title der Seite
   *
   * @var Tag
   */
  protected $title;

  /**
   * 
   * @var Tag
   */
  public $head;

  /**
   * 
   * @var Tag
   */
  public $body;
  
  /**
   * @var CSSManager
   */
  protected $cssManager;

  /**
   * @var JSManager
   */
  protected $jsManager;

  public function __construct(\Psc\JS\Manager $jsManager = NULL, \Psc\CSS\Manager $cssManager = NULL) {
    $this->html = new Tag('html', new stdClass);
    $this->html
      ->setAttribute('xmlns','http://www.w3.org/1999/xhtml')
      ->setAttribute('xml:lang',$this->language)
      ->setOption('br.beforeContent',TRUE)
    ;
    $this->title = new Tag('title');

    $this->head = new Tag('head',array());
    $this->head
      ->indent(2)
      ->setOption('br.beforeContent',TRUE)
    ;

    $this->head->content['title'] =& $this->title;
    
    $this->body = new Tag('body');
    $this->body
      ->setOption('br.beforeContent',TRUE)
      ->setOption('tag.indent',2)
      ;

    /* head und body to html (dies ist per reference) */
    $this->html->content->body = $this->body;
    $this->html->content->head = $this->head; 
    $this->setContentType($this->getContentType());
    $this->setMeta('content-language',$this->language);
    
    $this->html->template  = '%doctype%'."\n";
    $this->html->template .= '%self%';
    
    $this->html->contentTemplate .= '%head%'."\n";
    $this->html->contentTemplate .= '%body%'."\n";
    
    $this->attachCSSManager($cssManager ?: CSS::getManager());
    $this->attachJSManager($jsManager ?: JS::getManager());
    
    $this->setUp();
  }
  
  protected function setUp() {}
  
  /**
   * @return Tag<HTML>
   */
  public function getHTML() {
    $this->html->content->doctype = $this->doctype;
    
    /* CSS Manager */
    $this->attachCSS();
    
    /* JS Manager */
    $this->attachJS();
    
    return $this->html;
  }
  
  public function html() {
    return $this->getHTML();
  }
  
  /**
   * Missbraucht die Klasse als "Header" für eine HTML Datei
   *
   * wird dies gesetzt, endet das html bei <body> (öffnen)
   * es muss dann body und html von hand geschlossen werden
   *
   * $html = new \Psc\HTML\Page();
   * $html->setOpen();
   * print $html;
   * 
   * // hier goes the content
   * print '</body></html>';
   */
  public function setOpen() {
    $this->body->setOption('closeTag',FALSE);
    $this->html->setOption('closeTag',FALSE);
    return $this;
  }
  
  /**
   * Fügt der Seite einen Manager für die eingebundenen CSS Files hinzu
   */
  public function attachCSSManager(CSSManager $m) {
    $this->cssManager = $m;
    return $this;
  }


  /**
   * Fügt der Seite einen Manager für die eingebundenen JS Files hinzu
   */
  public function attachJSManager(JSManager $m) {
    $this->jsManager = $m;
    return $this;
  }
  
  protected function attachCSS() {
    if (isset($this->cssManager)) {
      foreach ($this->cssManager->getHTML() as $linkTag) {
        $this->head->content[$linkTag->getAttribute('href')] = $linkTag;
      }
    }
  }
  
  protected function attachJS() {
    if (isset($this->jsManager)) {
      foreach ($this->jsManager->getHTML() as $script) {
        $this->head->content[$script->getAttribute('src')] = $script;
      }
    }
  }
  
  /**
   * Setzt ein Meta Attribut
   * 
   * wird <var>$content</var> leer gelassen oder auf NULL gesetzt, wird das Meta Attribut gelöscht
   * @param string $name der Name des Meta Attributes
   * @param string $content der Wert des Meta Attributes
   * @return Tag<meta>
   */
  public function setMeta($name, $content = NULL, $httpEquiv = FALSE, $scheme = NULL) {
    $meta = HTML::Tag('meta');
    $meta->setOption('selfClosing',TRUE);

    if ($content === NULL) {
      /* löschen */
      $this->removeMeta($name);
    
    } else {
      /* im W3C steht "may be used in place" ... d.h. man könnte auch name und http-equiv gleichzeitig benutzen 
         http://www.w3.org/TR/html4/struct/global.html#edef-META
      */

      if (isset($name)) {
        if (!$httpEquiv) 
          $meta->setAttribute('name',$name);
        else
          $meta->setAttribute('http-equiv',$name);
      }
  
      if (isset($scheme))
        $meta->setAttribute('scheme',$scheme);

      if ($content !== FALSE) {
        $meta->setAttribute('content',$content);
      }

      /* meta tags liegen im head in der root ebene, d.h. wir suchen ob es dort schon ein meta tag gibt,
         wenn es eins gibt ersetzen wir dies, ansonsten fügen wir es hinzu 
      */
      /* schlüssel des meta tags suchen (wenn vorhanden) */
      $key = NULL;
      foreach ($this->head->content as $itemKey => $item) {
        if ($item instanceof Tag && $item->getTag() == 'meta' && ($item->getAttribute('name') == $name || $item->getAttribute('http-equiv') == $name)) {
          $key = $itemKey;
          break;
        }
      }
      
      if ($key !== NULL) {
        $this->head->content[$key] =& $meta;
      } else {
        $this->head->content[] =& $meta;
      }
    }
    return $meta;
  }
  
  public function removeMeta($name) {
    foreach ($this->head->content as $itemKey => $item) {
      if ($item instanceof Tag && $item->getTag() == 'meta' && ($item->getAttribute('name') == $name || $item->getAttribute('http-equiv') == $name)) {
        unset($this->head->content[$itemKey]);
        break;
      }
    }
  }
  
  /* Doofe Setter und Mini Funktionen */

  public function setContentType($contentType) {
    $this->contentType = $contentType;
    
    $ct = $this->contentType;
    if (isset($this->charset))
      $ct .= '; charset='.$this->charset;

    $this->setMeta('content-type',$ct, TRUE);
  }
  
  public function setCSSManager(CSSManager $m) { return $this->attachCSSManager($m);   }
  public function __toString() {
    try {
      return (string) $this->getHTML();
    } catch (\Exception $e) {
      // wir sehen hier eh nichts, wenn die exception kommt, die applikation stoppt immer
      // also geben wir hier den Fehler lieber aus und stoppen von selbst
      
      print $e;
      exit;
    }
  }
  
  /**
   * @return CSSManager
   */
  public function getCSSManager() { return $this->cssManager; }
  /**
   * @return JSManager
   */
  public function getJSManager() { return $this->jsManager; }
  
  /**
   * @return Tag
   */
  public function getTitle() {
    return $this->title; 
  }
  
  /**
   * Setzt das HTML Tag für den Title in <head> neu
   * Um den Titel mit einem String zu setzen:
   *
   * $html->getTitle->content = 'string';
   * @param Tag $title
   */
  public function setTitle(Tag $title) {
    $this->title = $title;
    return $this;
  }
  
  public function setTitleString($title) {
    $this->title = new Tag('title',HTML::esc($title));
    return $this;
  }
}
?>