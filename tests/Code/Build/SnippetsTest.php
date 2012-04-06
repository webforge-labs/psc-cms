<?php

namespace Psc\Code\Build;

use \Psc\Code\Build\Snippets;

class SnippetsTest extends \Psc\Code\Test\Base {
  
  public function testSnippet() {
    
    $snippet = new Snippets();
    $string = '/*%%MYVAR%%*/';
    
    $snippet->set('myvar','myvalue');
    $this->assertEquals('myvalue',$snippet->replace($string));
    
    $string = '-->/*%%MYVAR2%%*/<--';
    $snippet = new Snippets(array('MyVaR2'=>'myvalue2'));
    $this->assertEquals('-->myvalue2<--',$snippet->replace($string));
    
  }
}

?>