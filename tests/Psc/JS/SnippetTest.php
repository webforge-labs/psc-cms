<?php

namespace Psc\JS;

/**
 * @group class:Psc\JS\Snippet
 */
class SnippetTest extends \Psc\Code\Test\Base {
  
  protected $snippet;
  
  public function setUp() {
    $this->chainClass = 'Psc\JS\Snippet';
    parent::setUp();
    
  }
  
  public function testAcceptance() {
    $this->snippet = new Snippet("
      jQuery(document).ready(function () {
        var self = %self%;
        
        console.log(self, '%somevar%');
      });
    ",
    array('somevar'=>'test value',
          Snippet::VAR_NAME_SELF=>'main.getReferencedObject()'
          )
    );
    
    $this->assertInstanceOf('Psc\JS\Expression',$this->snippet);
    
    $this->assertEquals("
      jQuery(document).ready(function () {
        var self = main.getReferencedObject();
        
        console.log(self, 'test value');
      });
    ", $this->snippet->JS());
  }
  
  public function testCodeAsArray() {
    $expectedCode =
      "line1();\n".
      "line2();\n";
    $snippet = $this->createSnippet(array(
        'line1();',
        'line2();'
    ));

    $this->assertEquals($expectedCode, $snippet->JS());
  }

  // diese tests sind natürlich überhaupt nicht schön, aber besser als gar keine (vorsicht mit weißzeichen)
  public function testCodeWithRequire() {
    $this->assertContains("require(['Psc/UI/Dependency'], function(",
                          $this->createSnippet('no code', array())->setUse(array('Psc.UI.Dependency'))->JS());
  }

  public function testCodeWithEmbed() {
    $this->assertContains('<script type="text/javascript"',
                          (string) $this->createSnippet('no code', array())->html());
  }
  public function testCodeWithEmbedOnPsc() {
    $this->assertContains("requireLoad(",
                          $code = (string) $this->createSnippet('no code', array())->loadOnPscReady(TRUE)->html());
    
    $this->assertContains('<script type="text/javascript"', $code);
    
    // nicht 2 script tags
    $this->assertEquals(1, mb_substr_count($code, '</script>'));
  }
  
  protected function createSnippet($code, $vars = array()) {
    return new Snippet($code, $vars);
  }
}
?>