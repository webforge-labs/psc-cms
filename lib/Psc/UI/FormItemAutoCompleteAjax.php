<?php

namespace Psc\UI;

use \Doctrine\Common\Collections\ArrayCollection,
    \Psc\Code\Code,
    \Psc\JS\Code AS JSCode,
    \Psc\JS\Lambda,
    \Psc\JS\Helper AS JSHelper
;

class FormItemAutoCompleteAjax extends FormItemAutoComplete {
  
  protected $tabsType;
  
  /**
   * @var array
   */
  protected $ajaxData;
  
  /**
   * @param array $ajaxData wird mit den steuerdaten des autocompletes an den ajaxrequest übergeben
   */
  public function __construct($entityTabsType, Array $ajaxData = array()) {
    parent::__construct(NULL);
    
    $this->tabsType = $entityTabsType;
    $this->ajaxData = $ajaxData;
  }
  
  protected function chainSource() {
    return; 
  }
  
  
  protected function functions($options) {
    $options = parent::functions($options);
    $options['source'] = new Lambda("function( request, response) {
                                      var acInput = this.element;
                                      
                                      $.ajax({
                                        url : '/ajax.php?todo=ctrl&ctrlTodo=autoComplete',
                                        type: 'POST',
                                        global: false,
                                        dataType: 'json',
                                        data: ".JSHelper::convertHashMap(
                                                array_merge(
                                                  $this->ajaxData,
                                                  array(
                                                    'term'=>new JSCode('request.term'),
                                                    'type'=>$this->tabsType,
                                                    'data'=>$this->ajaxData
                                                  )
                                                )
                                              ).",
                                        success: function(data) {
                                             if (data.status == 'ok') {
                                               
                                               if (data.content.items.length == 0) {
                                                 $.pscUI('effects','blink',acInput);
                                               }
                                               
                                               response(data.content.items);
                                             }
                                             
                                             if (data.status == 'failure') {
                                               alert(data.content);
                                             }
                                          }
                                        });
                                    }");
    

    return $options;
  }
  
  public function setFunction($name,Lambda $function) {
    $this->functions[$name] = $function;
  }
}