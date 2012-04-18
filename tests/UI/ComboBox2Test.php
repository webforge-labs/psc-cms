<?php

namespace Psc\UI;

class ComboBox2Test extends \Psc\Code\Test\HTMLTestCase {
  
  protected $comboBox;
  
  protected $avaibleItems;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\ComboBox2';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $this->avaibleItems = $this->loadTestEntities('tags');
    $this->comboBox = new ComboBox2('tags', $this->avaibleItems);
    
    $this->html = $this->comboBox->html();
    
    $this->test->css('input.psc-cms-ui-combo-box',$this->html)
      ->count(1)
      ->hasAttribute('name', 'tags')
      ->hasAttribute('type','text')
    ;
    
    //file_put_contents('D:\out.txt', (string) $this->html);// copy that to Psc.UI.ComboBoxTest.js
    
    // @TODO how to test the javascript call?
    
    //require_once 'jparser.php';
    //
    //// kann nicht mehrere script tags
    //
    //$start = mb_strpos($this->html, $s = '<script type="text/javascript">')+mb_strlen($s)+1; // +1 wegen lineending
    //$end = mb_strrpos($this->html, '</script>');
    //$source = \Psc\String::substring($this->html, $start,$end);
    //
    ///**
    // * Get the full parse tree
    // */
    //try {
    //  $Prog = \JParser::parse_string( $source );
    //  //$Prog->dump( new \JLex );
    //  var_dump($Prog);
    //}
    //catch( \ParseError $Ex ){
    //    $error = $Ex->getMessage()."\n----\n".$Ex->snip( $source );
    //}
    //catch( \Exception $Ex ){
    //    $error = $Ex->getMessage();
    //}
  }
}
?>