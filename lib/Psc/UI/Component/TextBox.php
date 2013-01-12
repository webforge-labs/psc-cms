<?php

namespace Psc\UI\Component;

use Psc\UI\Form as f;
use Psc\UI\fHTML;

class TextBox extends \Psc\UI\Component\JavaScriptBase implements JavaScriptComponent {
  
  protected $capturesTab = FALSE;
  
  public function getInnerHTML() {
    $ta = fHTML::textarea($this->getFormName(),
                          $this->getFormValue(),
                          array('class'=>array('textarea','ui-widget-content','ui-corner-all'))
      )->setAttribute('cols',102)
       ->setAttribute('rows',6)
       ->setStyle('width', '90%')
    ;
    
    f::attachLabel($ta,$this->getFormLabel());
    
    return $ta;
  }
  
  public function getJavaScript() {
    if ($this->getCapturesTab()) {
      $snippet = $this->createSnippet(Array(
        'var component = '.self::JS_COMPONENT.', textarea = component.find("textarea");',
        "component.on('keydown', function (e) {",
        '  if (e.keyCode === 9 && !e.shiftKey && !e.ctrlKey && !e.altKey) {',
        '    e.preventDefault();',
        '    e.stopImmediatePropagation();',
        '    textarea.insertAtCaret("  ");',
        '  }',
        "});"
      ));
      
      return $snippet;
    }
  }
  
  /**
   * @return bool
   */
  public function getCapturesTab() {
    return $this->capturesTab;
  }
  
  /**
   * Setzt ob die TextArea bei Tab den Focus wechseln soll
   *
   * wenn TRUE wird statt dem Focus zu wechseln 2 Whitespaces eingefügt
   */
  public function setCapturesTab($bool) {
    $this->capturesTab = (bool) $bool;
    return $this;
  }
}
?>