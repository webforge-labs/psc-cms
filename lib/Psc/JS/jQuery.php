<?php

namespace Psc\JS;

use Psc\JS\Code as jsCode;

/**
 * @discouraged don't use the static helpers anymore!
 */
class jQuery extends \Webforge\DOM\Query {
    
  public static function factory($arg1, $arg2=NULL) {
    if ($arg2 !== NULL) {
      return new static($arg1, $arg2);
    } else {
      return new static($arg1);
    }
  }


  
  /* Statische Funktionen (Helpers) */
  public static function getIdSelector(\Psc\HTML\Tag $tag) {
    if ($tag->getAttribute('id') == NULL) $tag->generateId();
    
    return "jQuery('#".$tag->getAttribute('id')."')";
  }
  
  /**
   * @return string
   */
  public static function getClassSelector(\Psc\HTML\Tag $tag) {
    $guid = $tag->guid();
    $tag->publishGUID();
    
    if (mb_strpos($guid,'[') !== FALSE || mb_strpos($guid,']') !== FALSE) 
      return sprintf("jQuery('%s[class*=\"%s\"]')",$tag->getTag(),$guid);
    else
      return sprintf("jQuery('%s.%s')",$tag->getTag(),$guid);
  }
  
  /**
   * Fügt dem JavaScript des Tags eine weitere Funktion hinzu
   *
   * z. B. so:
   * <span id="element"></span>
   * <script type="text/javascript">$('#element').click({..})</script>
   *
   * @param Expression $code wird mit einem . an das Javascript des Selectors angehängt $code darf also am Anfang keinen . haben
   */
  public static function chain(\Psc\HTML\Tag $tag, Expression $code) {
    
    if (isset($tag->templateContent->jQueryChain)) {
      // append to previous created chain
      $tag->templateContent->jQueryChain .= sprintf("\n      .%s", $code->JS());
    
    } else {
      
      /*
       * make something like:
       *
       * require([...], function () {
       *   require([...], function (jQuery) {
       *     jQuery('selector for element')%jQueryChain%
       *   });
       * });
       */
      
      $tag->templateAppend(
        "\n".Helper::embedWithJQuery(
          new jsCode(
            '    '.self::getClassSelector($tag).'%jQueryChain%' // this is of course not valid js code, but it will be replaced to one
          )
        )
      );

      $tag->templateContent->jQueryChain = sprintf("\n      .%s", $code->JS());
    }
    
    return $tag;
  }
  
  public static function onWindowLoad($jsCode) {
    if ($jsCode instanceof \Psc\JS\Lambda) {
      $c = $jsCode->JS();
    } elseif ($jsCode instanceof \Psc\JS\Expression) {
      $c = 'function() { '.$jsCode->JS().' }';
    } else {
      $c = 'function() { '.(string) $jsCode.' }';
    }
      
    return Helper::embed('jQuery(window).load('.$c.')');
  }
}
