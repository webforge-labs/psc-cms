<?php

namespace Psc\PHPJS;

use \Psc\JS\Helper AS JSHelper;

/**
 * Objekt welches in ein Javascript Objekt überführt werden kann
 *
 * Wenn man eine Funktion in Javascript compilieren will, muss man in dieser Funktion nur ein paar Dinge beachten.
 *  - alle Referenzen auf $this sollten mit $this->refThis('variable'); ersetzt werden. Um zu testen Verhält sich im PHP Modus des Objektes dies so wie $this->variable
 *  - Man kann den Zustand des Objektes mit $this->isPHP() oder $this->isJS() abfragen. isJS() ist dann true, wenn das Objekt in Javascript Quellcode umgesetzt wird. Man kann also eigene Javascript-Implementationen angeben in dem man in seiner PHP Funktion auf isJS() überprüft und javascript quellcode zurückgibt
 *  - ansonsten nur PHP Funktionen erstmal compiliert werden die HTMLTag oder String zurückgeben
 *  
 */
abstract class CompilerObject extends \Psc\Object implements \Psc\PHPJSC\Object { // mind the C in PHPJSCObject
  
  const RETURNS_JS    =     0x000001;
  const CONSTRUCTOR   =     0x000002;
  
  /**
   * @var string js|php
   * @see isPHP(), isJS()
   */
  protected $state;
  
  public function &refThis($variable) {
    if ($this->isPHP())
      return $this->$variable;
    
    if ($this->isJS())
      return '[%[PHPJS-Reference:'.$variable.']%]';
      
  }
  
  public function isPHP() {
    return $this->state == 'php';
  }
  
  public function isJS() {
    return $this->state == 'js';
  }
  
  /**
   * @return string jscode
   */
  public function compileJS() {
    $jsTree = $this->getJSTree();
    $code = PHPJSCompilerHelper::compileJSTree($jsTree);
    
    return $code;
  }
  
  /**
   * @return array einen JS CompilerTree
   */
  public function getJSTree() {
    $methods = $this->getJSMethods();
    
    $br = "\n";
    $js = array('methods'=>array(),
                'properties'=>array(),
                'class'=>$this->getClass(),
                );
    
    foreach ($methods as $m => $flags) {
      $mBody = NULL;
      
      /* wir nehmen hier erstmal inflection, den das ist grad toller als ein Parser (was auch ginge) PHPParser */
      $reflMethod = new ReflectionMethod($this->getClass(),$m);
      
      /* wir übergeben als Parameter Referencen auf lokale Variablen */
      $params = $reflMethod->getParameters();
      
      $jsParams = array();
      foreach ($params as $param) {
        if ($param->isOptional()) {
          $mBody .= '  var '.$param->getName().' = '.$param->getName().' || '.JSHelper::convertValue($param->getDefaultValue()).';'.$br;
        }
        $jsParams[] = $param->getName();
      }

      if ($flags & self::RETURNS_JS) {
        $this->state = 'js';
        $value = $reflMethod->invokeArgs($this, $jsParams);
        $mBody = $value;
      } elseif($flags & self::RETURNS_HTML) {
        $value = $reflMethod->invokeArgs($this, $jsParams);
        if ($value instanceof HTMLTag) {
          $stringValue = $this->compileHTMLTag($value, JSHelper::QUOTE_SINGLE);
          $mBody .= sprintf("return %s; ".$br,$stringValue);
        }
      } else {
        
        continue;
      }
      
      if (($comment = $reflMethod->getDocComment()) == FALSE)
        $comment = NULL;
        
      $js['methods'][$m] = array('params'=>$jsParams,
                                 'body'=>$mBody,
                                 'name'=>$m,
                                 'comment'=>preg_replace('/^\s*\*/mu','  *',$comment), // normalisieren zu 2 whitespaces
                                );
    }
    
    if (!array_key_exists('toJSON',$js['methods'])) {
      $js['methods']['toJSON'] = array(
        'name'=>'toJSON',
        'params'=>array(''),
        'body'=>'var exp = this.phpdata;'.$br.'exp.__class = this.getClass();'.$br.'return exp;'.$br,
        'comment'=>'/** gibt die Daten des Objektes für JSON.stringify zurück */'
        );
    }
    
    if (!array_key_exists('getClass',$js['methods'])) {
      $js['methods']['getClass'] = array(
        'name'=>'getClass',
        'params'=>array(''),
        'body'=>'return '.JSHelper::convertString($this->getClass()).';'.$br,
        'comment'=>'/** gibt den Namen der Klasse des Objektes */'
        );
    }
    
    /* wir fügen noch die standard getter / setter der fields hinzu */
    foreach ($this->getJSFields() as $field) {
      $getter = 'get'.ucfirst($field);
      $setter = 'set'.ucfirst($field);
      if (!array_key_exists($getter,$js['methods'])) {
        $js['methods'][$getter] = array('params'=>array(),
                                  'body'=>'return '.$this->getJSReference($field).';'.$br,
                                  'name'=>$getter,
                                  'comment'=>'/** StandardGetter: '.$field.' */',
                                  );
      }
      
      if (!array_key_exists($setter, $js['methods'])) {
        $js['methods'][$setter] = array('params'=>array($field),
                                  'body'=>$this->getJSReference($field).' = '.$field.';'.$br,
                                  'name'=>$setter,
                                  'comment'=>'/** StandardSetter '.$field.' */',
                                  );
        
      }
      $property = new ReflectionProperty($this->getClass(),$field);
      $defaultValue = NULL; // not yet implemented
      
      if (($comment = $property->getDocComment()) == FALSE)
        $comment = NULL;
      
      $js['properties'][$field] = array('name'=>$this->getJSReference($field),
                                        'default'=>JSHelper::convertValue($defaultValue),
                                        'comment'=>preg_replace('/^\s*\*/mu','  *',$comment), // normalisieren zu 2 whitespaces
                                        );
    }


    return $js;
  }
  
  /**
   * Kompiliert ein HTMLTag zu einem Javascript Wert
   */
  protected function compileHTMLTag(HTMLTag $tag, $quote) {
    $html = (string) $tag;
    $js = JSHelper::convertString($html,$quote);
    
    /* wir haben hier einen String des HTMLs umschlossen mit $quote
       um unseren dynamischen Variablen der Funktion einzufügen, ersetzen wir diese mit referencen
       zur PHP data des JS Objektes
       
       wir unterbrechen an der stelle also den String und fügen mit der JS String Verkettungsfunktion + die Variablenreferenz
       ein:
       
       var html = " blasjflskdjflasdjflaskjflasdjflasd asldjflasdfjlasdfjalsdfjsldf "+this.phpdata.$variable+"
        asldfjsladfj sdlfjsldfjsldfjsldfjsldfjsdlfjsdlf ";
        
      indem falle ist $quote = '"';
    */
    return $this->replaceReferences($js, new \Psc\Code\Callback($this,'_helpCBConcatReference',array($quote)));
  }

  protected function _helpCBConcatReference($variable, $quote) {
    return $quote.'+'.$this->getJSReference($variable).'+'.$quote;
  }
  
  /**
   * Ersetzt die Referenzen im Code die durch $this->refThis() erstellt wurden
   *
   * @param string $code Code in JS der referencen auf objekte enthält
   * @param callback $cb erster dynamischer Parameter ist der Name der Variable gibt das replacement im Code zurück
   * @return string
   */
  protected function replaceReferences($code, $cb) {
    $variables = array();
    if (preg::match($code,'/\[%\[PHPJS-Reference:(.*?)\]%\]/g',$matches) > 0) {
      foreach ($matches as $m) {
        $variables[] = $m[1];
      }
      
      $variables = array_unique($variables);
      
      $search = array();
      $replace = array();
      foreach ($variables as $variable) {
        $search[] = '[%[PHPJS-Reference:'.$variable.']%]';
        $replace[] = $cb->call(array($variable));
      }
      
      $code = str_replace($search,$replace, $code);
    }
    return $code;
  }
  
  /**
   * Gibt den JSString für die Referenz zu einer JS PHP Data Variable zurück
   * @return string
   */
  protected function getJSReference($variable) {
    return 'this.phpdata.'.$variable;
  }
  
  
  public function convert() {
    $br = '';
    $ret = 'function () {'.$br;
    $ret .= '  var o = new '.$this->getClass().'();'.$br;
    foreach ($this->getJSFields() as $field) {
      $value = $this->$field;
      $ret .= '  o.set'.ucfirst($field).'('.JSHelper::convertValue($value).'); '.$br;
    }
    $ret .= '  return o; '.$br;
    $ret .= '}()'.$br;
    $ret .= $br;
    return $ret;
  }


}

?>