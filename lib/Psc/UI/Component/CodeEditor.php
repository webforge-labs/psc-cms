<?php

namespace Psc\UI\Component;

use Psc\UI\HTML;
use Psc\UI\Group;
use Psc\JS\JooseSnippet;

/**
 * 
 */
class CodeEditor extends JavaScriptBase implements JavaScriptComponent {
  
  /**
   * @var bool
   */
  protected $readonly = FALSE;
  
  public function getInnerHTML() {
    $ace = HTML::tag('div', NULL, array('class'=>'\Webforge\Common\ArrayUtilce-editor'));
    $group = new Group($this->getFormLabel(), $ace);
    $group->addClass('\Webforge\Common\ArrayUtilce-group');
    
    return $group;
  }
  
  public function getJavaScript() {
    return $this->createJooseSnippet(
      'Psc.UI.CodeEditor',
      array(
        'widget'=>$this->findInJSComponent('.psc-cms-ui-ace-editor'),
        'text'=>$this->getFormValue(),
        'readonly'=>$this->readonly,
        'formName'=>$this->getFormName(),
        ''
      )
    );
  }
  
  /**
   * @param bool $readonly
   */
  public function setReadonly($readonly) {
    $this->readonly = $readonly;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function getReadonly() {
    return $this->readonly;
  }
}
?>