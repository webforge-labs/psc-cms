<?php

namespace Psc\UI;

use \Doctrine\Common\Collections\ArrayCollection,
    \Psc\Code\Code,
    \Psc\JS\Code AS JSCode,
    \Psc\JS\Lambda
;

class TagsAutoComplete extends \Psc\Object implements AutoComplete {
  
  protected $minLength = 0;
  
  protected $delay = 300;
  
  /**
   * @var ArrayCollection
   */
  protected $tags;
  
  
  /**
   * Das TextElement (oder Textarea) auf das attach() aufgerufen wurde
   * 
   * @var Psc\HTML\Tag
   */
  protected $text;
  
  public function __construct($tags = NULL) {
    if (!isset($tags)) $tags = new ArrayCollection();
    if (is_array($tags)) $tags = new ArrayCollection($tags);
    
    $this->tags = $tags;
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
    
    foreach ($this->tags as $tag) {
      if ($tag instanceof \Psc\Form\Item) {
        $acData[] = array('label'=>$tag->getFormLabel(),
                          'value'=>$tag->getFormLabel()
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
      })"));
  }
  
  protected function functions($options) {
    $options['source'] = new Lambda("function( request, response ) {
                    response( $.ui.autocomplete.filter(
                       this.element.data('acData'), request.term.split(/,\s*/).pop() ) );
                }");
    
    $options['focus'] = new Lambda("function() {
                    // prevent value inserted on focus
                    return false;
                }");
    
    $options['select'] = new Lambda("function( event, ui ) {
                    var terms = this.value.split(/,\s*/);
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push( ui.item.value );
                    // add placeholder to get the comma-and-space at the end
                    terms.push( '' );
                    this.value = terms.join( ', ' );
                    return false;
                }");
    return $options;
  }
}