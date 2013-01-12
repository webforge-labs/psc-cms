<?php

namespace Psc\Code\AST;

/**
 * @group class:Psc\Code\AST\Walker
 */
class WalkerTest extends \Psc\Code\Test\Base {
  
  protected $walker;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\AST\Walker';
    parent::setUp();
    $this->codeWriter = $this->getMockForAbstractClass('Psc\Code\AST\CodeWriter');
    $this->walker = new Walker($this->codeWriter);
    $this->jsWalker = new Walker(new \Psc\JS\AST\CodeWriter());
    $this->dsl = new ExampleDSL();
  }
  
  public function testVariableDefinition() {
    $this->codeWriter->expects($this->once())->method('writeStatement');
    $this->codeWriter->expects($this->once())->method('writeVariableDefinition')->with($this->equalTo('$myvar'), $this->equalTo('myvalue'));
    $this->codeWriter->expects($this->once())->method('writeValue')->with($this->equalTo('myvalue'))->will($this->returnValue('myvalue'));
    $this->codeWriter->expects($this->once())->method('writeVariable')->with($this->equalTo('myvar'))->will($this->returnValue('$myvar'));
    
    $this->assertWalk('Statement', $this->dsl->var_('myvar','String', 'myvalue'),
                      'var myvar = "myvalue";'."\n"
                     );
  }
  
  public function testConstructExpression() {
    $js = <<<'JAVASCRIPT'
new Psc.UI.Main({
  'tabs': new Psc.UI.Tabs({
    'widget': $('#psc-ui-tabs')
  })
})
JAVASCRIPT;

    $this->codeWriter->expects($this->exactly(2))->method('writeClassName')->with($this->isType('string'))->will($this->returnArgument(0));
    $this->codeWriter->expects($this->exactly(2))->method('writeArguments')->with($this->isType('array'))->will($this->returnValue('eins, zwei'));
    $this->codeWriter->expects($this->exactly(2))->method('writeConstructExpression')->with($this->isType('string'),$this->isType('string'));
    $this->codeWriter->expects($this->exactly(2))->method('writeHashMap')->with($this->isType('object'));

    $this->assertWalk('ConstructExpression', $this->dsl->exampleConstruct(),
                      $js
                      );
  }
  
  public function assertWalk($method, $part, $js) {
    $method = 'walk'.$method;
    $this->walker->$method($part);
    $this->assertJavaScriptEquals($js, $this->jsWalker->$method($part));
  }  
}
?>