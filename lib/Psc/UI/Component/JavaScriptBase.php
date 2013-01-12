<?php

namespace Psc\UI\Component;

use Psc\JS\Snippet;
use Psc\JS\JooseSnippet;

abstract class JavaScriptBase extends Base {
  
  const JS_COMPONENT = Snippet::VAR_SELF;
  
  protected $html;
  
  /**
   * Erstellt ein Snippet welches mit %self% auf die Componente als jQuery Objekt zugreifen kann
   * 
   * @return Psc\JS\Snippet
   */
  protected function createSnippet($code = '', Array $vars = array()) {
    return new Snippet($code, $vars);
  }

  /**
   * Erstellt ein Snippet welches mit %self% auf die Componente als jQuery Objekt zugreifen kann
   * 
   * @return Psc\JS\Snippet
   */
  protected function createJooseSnippet($jooseClass, $constructParams = NULL, Array $dependencies = array()) {
    return JooseSnippet::create($jooseClass, $constructParams, $dependencies);
  }
  
  // damit jooseBase und JavaScriptBase wenigstens einheitlich sind
  // denn das hier hat nix mit joose zu tun
  protected function widgetSelector(\Psc\HTML\Tag $tag = NULL, $subSelector = NULL) {
    $jQuery = \Psc\JS\jQuery::getClassSelector($tag ?: $this->html);
    
    if (isset($subSelector)) {
      $jQuery .= sprintf(".find(%s)", \Psc\JS\Helper::convertString($subSelector));
    }
    
    return $this->jsExpr($jQuery);
  }
  
  protected function findInJSComponent($selector) {
    return $this->jsExpr(self::JS_COMPONENT.sprintf(".find(%s)", \Psc\JS\Helper::convertString($selector)));
  }
  
  /**
   * Erstellt eine Javascript-Expression
   * 
   * $this->createJooseSnippet(
   *  'Psc.UploadService',
   *  array(
   *    'apiUrl'=>'/',
   *    'uiUrl'=>'/',
   *    'ajaxService'=>$this->jsExpr('main')
   *  )
   *  
   * führt $jsCode wortwörtlich aus
   */
  protected function jsExpr($jsCode) {
    return JooseSnippet::expr($jsCode);
  }
  
  public function wrapHTML($componentContent) {
    $this->html = $wrapper = parent::wrapHTML($componentContent);
    
    if (($snippet = $this->getJavaScript($wrapper)) instanceof \Psc\JS\Snippet) {
      $snippet = clone $snippet;
      $snippet->setVar(Snippet::VAR_NAME_SELF, \Psc\JS\jQuery::getClassSelector($wrapper));
      $wrapper->getContent()->js = $snippet->html();
    }
    
    return $wrapper;
  }
}
?>