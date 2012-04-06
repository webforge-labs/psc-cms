<?php

namespace Psc;

use \Psc\String AS S;

class StringTest extends \Psc\Code\Test\Base {

  public function testIndent() {
    
    $string = 'aaa';
    $expect = '  aaa';
    $this->assertEquals($expect,S::indent($string,2));
    
    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc\n";
    
    $expect  = "  aaa\n";
    $expect .= "  bbb\n";
    $expect .= "  cccc\n";
    $this->assertEquals($expect,S::indent($string,2));

    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc";

    $expect  = "  aaa\n";
    $expect .= "  bbb\n";
    $expect .= "  cccc";
    $this->assertEquals($expect,S::indent($string,2));
    
    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc\n";
    $this->assertEquals($string,S::indent($string,0));
  }
  
  public function testPrefix() {
    $string = 'aaa';
    $expect = '[prefix]aaa';
    $this->assertEquals($expect,S::prefixLines($string,'[prefix]'));
    
    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc\n";
    
    $expect  = "[prefix]aaa\n";
    $expect .= "[prefix]bbb\n";
    $expect .= "[prefix]cccc\n";
    $this->assertEquals($expect,S::prefixLines($string,'[prefix]'));

    $string  = "\r\naaa\r\n";
    $string .= "bbb\r\n";
    $string .= "cccc\r\n";
    
    $expect  = "[prefix]\r\n[prefix]aaa\r\n";
    $expect .= "[prefix]bbb\r\n";
    $expect .= "[prefix]cccc\r\n";
    $this->assertEquals($expect,S::prefixLines($string,'[prefix]'));
  }
}
?>