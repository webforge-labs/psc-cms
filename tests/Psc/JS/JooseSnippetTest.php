<?php

namespace Psc\JS;

/**
 * @group class:Psc\JS\JooseSnippet
 */
class JooseSnippetTest extends \Psc\Code\Test\Base {
  
  protected $jooseSnippet;
  
  public function setUp() {
    $this->chainClass = 'Psc\JS\JooseSnippet';
    parent::setUp();
    $this->jooseSnippet = new JooseSnippet(
      'Psc.UI.Main',
      (object) array('tabs'=>JooseSnippet::create('Psc.UI.Tabs',
                                                  (object) array('widget'=>JooseSnippet::expr("$('#psc-ui-tabs')"))
                                                  )
                    )
    );
  }
  
  public function testLanguageAST() {
    $dsl = new \Psc\Code\AST\DSL();
    extract($dsl->getClosures());
    
    $expectedAST = $var('j', NULL,
                        $construct(
                          'Psc.UI.Main',
                          $arguments(
                            $argument(
                              (object) array('tabs'=>$construct(
                                                      'Psc.UI.Tabs',
                                                      $arguments(
                                                        $argument((object) array('widget'=>JooseSnippet::expr("$('#psc-ui-tabs')")))
                                                      )
                                                    )
                                            )
                            )
                          )
                        )
                       );
    
    $this->assertEquals($expectedAST, $this->jooseSnippet->getAST());
  }
  
  public function testAcceptance() {
    $js = <<<'JAVASCRIPT'
requireLoad(['jquery', 'Psc/UI/Main', 'Psc/UI/Tabs'], function(main,jQuery) {
  var j = new Psc.UI.Main({
    'tabs': new Psc.UI.Tabs({
      'widget': $('#psc-ui-tabs')
    })
  });
});
JAVASCRIPT;
    
    $this->assertJavascriptEquals($js, $this->jooseSnippet->JS()); 
  }
}
?>