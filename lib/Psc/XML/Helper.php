<?php

namespace Psc\XML;

use DOMNodeList;
use DOMElement;
use DOMDocument;
use Symfony\Component\CssSelector\CssSelector;

class Helper {
  
  /**
   *
   * @param string $html ein HTML Dokument (mit header und allem, wichtig damit encoding richtig gesetzt wird)
   * @return DOMDocument
   */
  public static function doc($html) {
    if (!is_string($html))
      $html = self::export($html);
    
    $reporting = error_reporting(0);
    $dom = new \DOMDocument();
    $dom->loadHTML($html);
    libxml_use_internal_errors(TRUE);
    error_reporting($reporting);
    
    return $dom;
  }
  
  /**
   * Ergänzt das $html zu einem Document und parsed dieses
   *
   * nimmt als default
   * <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">';
   an
   * @param string $html ein HTML Schnipsel
   */
  public static function docPart($htmlPart, $encoding = 'utf-8', $docType = NULL) {
    if (!is_string($htmlPart))
      $htmlPart = self::export($htmlPart);
    
    if (mb_strpos(trim($htmlPart), '<!DOCTYPE') === 0) {
      $document = $htmlPart;
    } else {
      // DOMDocument setzt so oder so einen default, also können wir das auch explizit machen
      $docType = $docType ?: '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';

      // ebenso das encoding ist utf8
      $document  = $docType."\n";
      
      if (mb_strpos(trim($htmlPart),'<html') === 0) {
        $document .= $htmlPart;
      } else {
        $document .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset='.$encoding.'"/></head><body>'.$htmlPart.'</body></html>';
      }
    }
    
    return self::doc($document);
  }
  
  /**
   * @param string jQuery-Like Selector
   * @return array
   */
  public static function query(DOMDocument $dom, $sel) {
    $xpath = new \DOMXPath($dom);
    
    $ret = array();
    $expression = CssSelector::toXpath($sel);
    $result = $xpath->query($expression);
    
    if (!($result instanceof \DOMNodeList)) {
      throw new Exception('Symfony Parser erzeugte eine invalide XPath-Expression: '.$sel.' => '.$expression);
    }
    
    foreach ($result as $node) {
      $ret[] = $node;
    }
    return $ret;
  }
  
  /**
   * @param string xpath
   * @return array
   */
  public static function xpath(DOMDocument $dom, $path) {
    $xpath = new \DOMXPath($dom);
    
    $ret = array();
    foreach ($xpath->query($path) as $node) {
      $ret[] = $node;
    }
    return $ret;
  }
  
  public static function export($item) {
    if ($item instanceof DOMDocument) {
      $out = $item->saveXML();
    } elseif ($item instanceof DOMNodeList) {
      $out = array();
      foreach ($item as $node) {
        $out[] = self::export($node);
      }
    } elseif ($item instanceof \DOMElement || $item instanceof \DOMComment) {
      $out = $item->ownerDocument->saveXML($item);
    } elseif ($item instanceof \DOMText) {
      $out = $item->wholeText;
    } elseif (is_array($item)) {
      $out = array();
      foreach ($item as $node) {
        $out[] = self::export($node);
      }
    } else {
      $out = $item;
    }
    
    return $out;
  }
  
  public static function dump($item) {
    var_dump(self::export($item));
  }
  
}