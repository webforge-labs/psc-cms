<?php

namespace Psc\Form;

use \Psc\Form\DataInput AS FormDataInput;

/**
 * @group class:Psc\Form\DataInput
 */
class DataInputTest extends \Psc\Code\Test\Base {
  
  public function cons($POST, $expected, $line = NULL) {
    $input = new \Psc\Form\DataInput($POST);
    $this->assertEquals($expected,$input->getData(),'Zeile: '.$line);
    return $input;
  }

  public function testConstruct () {
    $this->cons(
                array(
                      'string'=>'string',
                      'empty'=>''
                      ),
                array(
                      'string'=>'string',
                      'empty'=>NULL
                      ),
                __LINE__
                );
    
    $this->cons(
      array(),
      array(),
      __LINE__
      );
  
    $this->cons(
      array('key1' =>' ',
            'key2' => '      s  '),
      array('key1' =>NULL,
            'key2' => '      s  '),
      __LINE__
      );
    
    $this->cons(
      array('keys'=>array('key1'=>' ','key2'=>'leer')),
      array('keys'=>array('key1'=>NULL,'key2'=>'leer')),
      __LINE__
    );

    $this->cons(
      array('keys'=>array('key1'=>' ','key2'=>'leer'),'key3'=>'    '),
      array('keys'=>array('key1'=>NULL,'key2'=>'leer'),'key3'=>NULL),
      __LINE__
    );

    $this->cons(
      array('keys'=>array('key1'=>' ','key2'=>'leer'),'key3'=>'    ','keys2'=>array('key1'=>' ','key2'=> ' ')),
      array('keys'=>array('key1'=>NULL,'key2'=>'leer'),'key3'=>NULL,'keys2'=>array('key1'=>NULL,'key2'=>NULL)),
      __LINE__
    );
  }
  
  public function testgetDataWithKeys() {
    $data = $this->cons(
      array('keys'=>array('key1'=>' ','key2'=>'leer'),'key3'=>'    ','keys2'=>array('key1'=>' ','key2'=> ' ')),
      array('keys'=>array('key1'=>NULL,'key2'=>'leer'),'key3'=>NULL,'keys2'=>array('key1'=>NULL,'key2'=>NULL))
                );
    
    $this->assertEquals(NULL,$data->getDataWithKeys(array('keys','key1')));
  }
  
  public function testGetDoAndDefault() {
    $data = $this->cons(
      array('keys'=>array('key1'=>' ','key2'=>'nichtleer'),'key3'=>'    ','keys2'=>array('key1'=>' ','key2'=> ' '),'nil'=>NULL,'nil2'=>' '),
      array('keys'=>array('key1'=>NULL,'key2'=>'nichtleer'),
            'key3'=>NULL,
            'keys2'=>array('key1'=>NULL,'key2'=>NULL),
            'nil'=>NULL,
            'nil2'=>NULL)
    );
    
    $this->assertEquals(NULL, $data->get(array('keys','nonexistsn'), FormDataInput::RETURN_NULL));
    $this->assertEquals(array(), $data->get(array('keys','nil'), array()));
    $this->assertEquals(array(), $data->get(array('keys','nil2'), array()));
    
    $this->assertEquals('default', $data->get(array('keys','key1'), 'default'));
    $this->assertEquals('default', $data->get(array('keys','key3'), 'default'));
    $this->assertEquals('default', $data->get(array('keys','notexistent'), 'default'));
    $this->assertEquals('nichtleer', $data->get(array('keys','key2'), 'default'));
    $this->assertEquals(array('key1'=>NULL,'key2'=>NULL), $data->get('keys2', 'default'));

    try {
      $data->get('nonexists');
    } catch (\Exception $e) {
      $this->assertInstanceOf('\Psc\Form\DataInputException',$e);
    }
  }
  
  /**
   * @expectedException \InvalidArgumentException
   */
  public function testGetDataWithKeysException() {
    $data = $this->cons(
      array('key0'=>array('key1'=>' ','key2'=>'leer'),'key3'=>'    ','keys2'=>array('key1'=>' ','key2'=> ' ')),
      array('key0'=>array('key1'=>NULL,'key2'=>'leer'),'key3'=>NULL,'keys2'=>array('key1'=>NULL,'key2'=>NULL))
                );
    
    /* aufruf mit array als keys, was natürlich nicht geht */
    $data->getDataWithKeys(array('key0',array('bla')));
  }
}