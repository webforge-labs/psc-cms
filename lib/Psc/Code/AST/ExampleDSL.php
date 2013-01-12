<?php

namespace Psc\Code\AST;

class ExampleDSL extends DSL {

  public function exampleArguments() {
    extract($this->getClosures());
    
    return $arguments(
      $argument($value('i am value 1')),
      $argument($value('i am value 2'))
    );
  }
  
  /**
   * @cc-ignore
   */
  public function exampleVariableDefinition() {
    return $this->var_('myvar', 'String', 'myvalue');
  }
  
  /**
   * @cc-ignore
   */
  public function exampleConstruct() {
    extract($this->getClosures());
    
    return $construct(
      'Psc.UI.Main',
      $arguments(
        $argument(
          $hashMap(array('tabs'=>$construct(
                                  'Psc.UI.Tabs',
                                  $arguments(
                                    $argument($hashMap(array('widget'=>$expression("$('#psc-ui-tabs')"))))
                                  )
                                )
                        )
                  )
        )
      )
    );
  }
}
?>