<?php

namespace Psc\System;

class LocaleHelper {
  
  public static function toLanguage($locale) {
    $pre = mb_substr($locale, 0, 2);
    if (in_array($pre, array('de','en','fr'))) {
      return $pre;
    }
    
    throw new \Psc\Exception(sprintf("Cannot parse language from '%s'", $locale));
  }
}
?>