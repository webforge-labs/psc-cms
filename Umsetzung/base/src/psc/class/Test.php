<?php

class Test 
extends Object implements 
banane, 
tanne, 
palme {

  const CONSTANT1 = 'value';

  const CONSTANT2 = 'value'; // ein kommentar

  const CONSTANT3 = 10;

  const CONSTANT4 = array('hier','ist','ein','statischer','array','der',
    array('sogar', 'einen', array('weiteren','fiesen',
        'unterrarray',
        'hat')
    )
  );

  /**
   * hier ist auch ein komment
   * 
   */
  protected $prop1 = 'banane';

  // dazwischen
  protected static $prop2 = 'flippflapp'; // dahinter
  /**
   * hier sit ein comment
   * 
   */  
  protected $prop3 = 'wunselbunsel';


  /**
   * hier sit noch ein ein comment
   * 
   */  
  protected $prop4;


  protected $prop5 = NULL;


  protected $prop6 = array('data');

  private function c_getterAndSetter(PHPCompiler $compiler, PHPWriter $writer) {

    foreach ($compiler->getProperties($this) as $propertyName => $property) {

      $writer->addMethod('set'.ucfirst($propertyName),'');

    }
    
  }


  private function c_databaseModel(PHPCompiler $compiler, PHPWriter $writer) {
    $writer->addCodeToConstructor(
      PHPWriter::variable('$this->tables',$this->tables).$writer->eol
      .$writer->eol
      .PHPWriter::variable('$this->columns',$this->columns).$writer->eol
    );
  }


}


?>