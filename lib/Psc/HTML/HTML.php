<?php

namespace Psc\HTML;

class HTML {
  
  const NO_LOWER_ATTRIBUTES = 0x000001;

  const BLOCK = 'block';
  const INLINE = 'inline';
  
  public static function esc($value) {
    return htmlspecialchars($value,ENT_NOQUOTES, 'UTF-8');
  }
  
  public static function escAttr($value) {
    return htmlspecialchars($value,ENT_QUOTES, 'UTF-8');
  }

  
  /**
   * Erstetzt alle nicht erlaubten zeichen aus dem String mit legitimen für ein id="" attribute
   *
   * wird in GUID in tag benutzt
   * in Tabs2 zum erstellen der id für den tab
   * usw
   */
  public static function string2id($stringValue) {
    return str_replace(array('.','@','\\',' '),array('_','_','_','-'),$stringValue);
  }
  
  /**
   * Erstells aus einem CamelCase Namen oder einer Klasse oder einem String eine html-klasse
   */
  public static function string2class($stringValue) {
    $stringValue = trim($stringValue);
    
    if ($stringValue === '') {
      return $stringValue;
    }
    
    $specials = preg_quote(implode("", array('.','@','\\',' ','[',']','(',')')), '/');
    
    $stringValue = \Psc\Preg::replace(// in
                      $stringValue,
                      // what
                      sprintf('/%s|[%s]/',
                                "(?<=\w)([A-Z]|[0-9])",
                                $specials
                              ),
                       // with
                       '-\\1'
                  );
    $stringValue = mb_strtolower($stringValue);
    
    return $stringValue;
  }
  
  /**
   * @return HTMLTag
   * @see HTMLTag::__construct()
   */
  public static function tag($name, $content = NULL, Array $attributes = NULL, $flags = 0x000000) {
    $tag = new Tag($name, $content, $attributes, $flags);
    return $tag;
  }
}

?>