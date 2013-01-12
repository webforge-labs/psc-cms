<?php

namespace Psc\UI;

use \Doctrine\Common\Collections\ArrayCollection,
    \Psc\Code\Code,
    \Psc\JS\Code AS JSCode,
    \Psc\JS\Lambda
;

class FormItemAutoComplete extends \Psc\Object implements AutoComplete {
  
  protected $minLength = 0;
  
  protected $delay = 300;
  
  /**
   * @var ArrayCollection<\Psc\Form\Item>
   */
  protected $items;
  
  /**
   * Das TextElement (oder Textarea) auf das attach() aufgerufen wurde
   * 
   * @var Psc\HTML\Tag
   */
  protected $text;
  
  protected $labelHook;
  
  protected $functions = array();
  
  public function __construct($items = NULL) {
    if (!isset($items)) $items = new ArrayCollection();
    if (is_array($items)) $items = new ArrayCollection($items);
    
    $this->items = $items;
  }
  
  public function attach(\Psc\HTML\Tag $inputText) {
    $this->text = $inputText;
    
    $this->chainBind();
    $this->chainSource();
    jQuery::widget($this->text, 'autocomplete', $this->getOptions());
    
    return $this->text;
  }

  public function getOptions() {
    $options = array();
    foreach (array('minLength','delay') as $o) {
      $options[$o] = $this->$o;
    }
    $options = $this->functions($options);
    
    return $options;
  }
  
  protected function chainSource() {
    $acData = array();
    
    $lh = $this->labelHook;
    /* kopiert nach AutoCompleteResponse*/
    foreach ($this->items as $item) {
      if ($item instanceof \Psc\Form\Item) {
        $acData[] = array('label'=>($lh instanceof \Closure) ? $lh($item) : $item->getFormLabel(),
                          'value'=>$item->getFormValue()
                      );
      } else {
        throw new \Psc\Exception('Nur \Psc\Form\Item benutzbar in ArrayCollection für Tags');
      }
    }
    
    jQuery::chain($this->text, new JSCode("data('acData', ".json_encode($acData).")"));
  }
  
  protected function chainBind() {
    
    // don't navigate away from the field on tab when selecting an item
   jQuery::chain($this->text, new JSCode("bind( 'keydown', function(e) {
     if (e.keyCode === $.ui.keyCode.TAB &&
        $(this).data('autocomplete').menu.active) {
         e.preventDefault();
        }

     if (e.keyCode === $.ui.keyCode.ENTER) {
       e.preventDefault();
     }
    })"));
  }
  
  protected function functions($options) {
    $options['source'] = new Lambda("function( request, response ) {
                                      var items = $.ui.autocomplete.filter(this.element.data('acData'), request.term);
                                      
                                      if (items.length == 0) {
                                        $.pscUI('effects','blink',this.element);
                                      }

                                      response(items);
                                    }");
    
    $options['focus'] = new Lambda("function() {
                    // prevent value inserted on focus
                    return false;
                }");
    
    //$options['select'] = new Lambda("function( event, ui ) {
    //                var terms = this.value.split(/,\s*/);
    //                // remove the current input
    //                terms.pop();
    //                // add the selected item
    //                terms.push( ui.item.value );
    //                // add placeholder to get the comma-and-space at the end
    //                terms.push( '' );
    //                this.value = terms.join( ', ' );
    //                return false;
    //            }");
    
    $options = array_merge($options,$this->functions);
    
    return $options;
  }
  
  public function setFunction($name,Lambda $function) {
    $this->functions[$name] = $function;
  }
}