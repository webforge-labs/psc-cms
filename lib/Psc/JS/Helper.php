<?php

namespace Psc\JS;

use Psc\Code\Code AS C;
use Psc\HTML\HTML;
use Webforge\Common\ArrayUtil AS A;

class Helper extends \Psc\Object {
  
  const QUOTE_SINGLE = "'";
  const QUOTE_DOUBLE = '"';
  
  public static function convertArray(Array $array, $quote = self::QUOTE_SINGLE) {
    $type = (count(array_filter(array_keys($array),'is_string')) > 0) ? 'assoc' : 'numeric';
    
    if ($type == 'assoc')
      return self::convertHashMap((object) $array, $quote = self::QUOTE_SINGLE);

    if (count($array) == 0)
      return '[]';
      
    $js  = '[';
    foreach ($array as $data) {
      $js .= self::convertValue($data, $quote).',';
    }
    $js = mb_substr($js,0,-1);
    $js .= ']';
    return $js;
  }
  
  /**
   * @return string wird mit $quote umgeben sein
   */
  public static function convertValue($data, $quote = self::QUOTE_SINGLE) {
    if ($data === NULL)
      return 'null';
    
    if ($data === array())
      return '[]';

    if ($data instanceof \Psc\JS\Expression) {
      return $data->JS();
    }

    if ($data instanceof \Psc\PHPJS\CompilerObject) {
      $object = $data;
      return $object->convert();
    }
    
    switch (C::getType($data)) {
      case 'int':
        return $data;
      
      case 'bool':
        return $data == TRUE ? 'true' : 'false';
      
      case 'array':
        return self::convertArray($data, $quote);
      
      case 'object':
      case 'object:stdClass':
        return self::convertHashmap($data, $quote); // wieder in converthashmap geändert (nicht mehr json_encode(), weil hier \Psc\JS\Code in {} umgewandelt wurde)
        
      case 'string':
      case 'unknown type':
      case 'float': // php tut hier das richtige für uns
        return self::convertString((string) $data, $quote);
        
      case 'resource':
      default:
        return 'null';
    }
  }
  
  /**
   * Gibt einen Javascript String für string zurück
   *
   * wird auch als hack genommen um verchachtelte objekte zu exportieren (oh oh, but works)
   * @return string
   */
  public static function convertString($data, $quote = self::QUOTE_SINGLE) {
    return json_encode($data);
  }
  
  /**
   * Wandelt ein Object in ein Javascript Object-Literal um
   * 
   * @return string
   */
  public static function convertHashMap(\stdClass $object, $quote = self::QUOTE_SINGLE) {
    $hash = '{';
    
    $vars = get_object_vars($object);
    if (count($vars) > 0) {
      foreach ($vars as $key =>$value) {
        $hash .= sprintf('"%s": %s,', $key, self::convertValue($value,$quote)); // json encode kanns auch wenn wir statt single quotes double quotes für die keys nehmen
      }
      $hash = mb_substr($hash,0,-1); // letzte komma
    }
    
    $hash .= '}';
    return $hash;
  }
  
  public static function embed($jsCode) {
    return HTML::tag('script',self::convertJSCode($jsCode),array('type'=>'text/javascript'))
      ->setOption('br.beforeContent',TRUE)
      ->setOption('br.afterContent',TRUE)
      ->setOption('br.closeTag',TRUE)
    ;
  }
  
  public static function requirejs($requirements, $alias = NULL, $jsCode = NULL) {
    if (!isset($jsCode)) {
      return sprintf('require([%s]);', self::buildRequirements((array) $requirements));
    } else {
      return sprintf(
        "require([%s], function(%s) {\n  %s\n});",
        self::buildRequirements((array) $requirements),
        implode(', ', (array) $alias),
        self::convertJSCode($jsCode)
      );
    }
  }
  
  protected static function buildRequirements(Array $requirements) {
    return A::implode(
      $requirements,
      ', ',
      function ($requirement) {
        return sprintf("'%s'", $requirement);
      }
    );
  }
  
  /**
   * Use this embedding if you only need jQuery in your inline code and want to initiate a widget or something
   *
   * for example: calling the widget for a button
   */
  public static function embedWithJQuery($jsCode) {
    return self::embed(
      self::requirejs('boot', array(),
        self::requirejs('jquery', 'jQuery', $jsCode)
      )
    );
  }

  /**
   * This function loads inline script in blocking mode
   *
   * the application has to inject window.requireLoad
   * this is a blocking way to load, because there is the requireLoad() function which is directly called,
   * which concatenates the job to the boot.getLoader()  the jobs can then be loaded with boot.getLoader().finished()
   */
  public static function requireLoad(Array $requirements, Array $alias, $jsCode) {
    return sprintf(
      "requireLoad([%s], function (main%s) { // main is unshifted automatically\n  %s\n});",
      self::buildRequirements($requirements),
      count($alias) > 0 ? ', '.implode(', ', $alias) : '',
      self::convertJSCode($jsCode)
    );
  }
  
  /**
   * This function loads inline script in an HTML-page
   *
   * this is NON blocking way to load, because there the require is nested here
   * use it in inline scripts which are directly loaded from the HTML on the index page (and only there)
   *
   * boot is required
   * boot should have .getLoader() to attach the requirements and loading callback to
   * @return string
   */
  public static function bootLoad(Array $requirements, $alias = array(), $jsCode) {
    return self::requirejs('boot', 'boot', sprintf(
      "boot.getLoader().onRequire([%s], function (%s) {\n    %s\n  });",
      self::buildRequirements($requirements),
      implode(', ', $alias),
      self::convertJSCode($jsCode)
    ));
  }
  
  /**
   * Returns a Script-Tag for a script file
   */
  public static function load($jsFile) {
    return HTML::tag('script',NULL,array('type'=>'text/javascript','src'=>$jsFile))
      //->setOption('tag.indent',0) 
      ->setOption('br.closeTag',TRUE)
    ;
  }

    // legacy
  public static function embedOnReady($jsCode) {
    return self::embed('jQuery(document).ready(function($) {'.self::convertJSCode($jsCode).'})');
  }
  
  public static function convertJSCode($jsCode) {
    if (is_string($jsCode)) {
      return $jsCode;
    }

    if (is_array($jsCode)) {
      $jsCode = implode("\n",$jsCode);
    }

    if ($jsCode instanceof \Psc\JS\Expression)
      $jsCode = $jsCode->JS();
    
    if ($jsCode instanceof \Psc\PHPJSC\Object)
      $jsCode = $jsCode->compileJS();

    return $jsCode;
  }

  public static function reformatJSON($json) {
    return JSONConverter::create()->prettyPrint($json);
  }
}
