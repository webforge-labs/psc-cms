<?php

use \Psc\DataInput AS DataInput;

/**
 * @group cache
 * weil so viele Cache-Klassen hiervon abhängen
 */
class DataInputTest extends \Psc\Code\Test\Base {
  
  protected $tdata; // data für die throw blöcke
  
  public function SetUp() {
    $this->tdata = $this->cons(
      array('dataKey'=>'datahier','emptyKey'=>' '),
      array('dataKey'=>'datahier','emptyKey'=>NULL)
    );
    $this->chainClass = 'Psc\DataInput';
    
    parent::setUp();
  }
  
  public function cons($data, $expected, $line = NULL) {
    $input = new \Psc\DataInput($data);
    $this->assertEquals($expected,$input->getData(),'Zeile: '.$line);
    return $input;
  }

  public function testConstruct () {
    new \Psc\DataInput();
  }
  
  /**
   * @expectedException Psc\DataInputException
   */
  public function testSetDataWithEmptyKeys() {
    $data = array();
    $input = new DataInput($data);
    
    /* nicht erlaubt da sonst data kein array mehr wäre */
    $input->setDataWithKeys(array(), 'nodata');
  }
  
  public function testSetDataWithKeys() {
    $data = array();
    $input = new DataInput($data);

    $input->setDataWithKeys(array(), array('key1'=>'key1value'));
    $this->assertEquals('key1value',$input->get('key1'));
    $this->assertEquals(array('key1'=>'key1value'),$input->get(array()));
  }
  
  public function testgetDataWithKeys() {


    $data = $this->cons(
      array('keys'=>array('key1'=>' ','key2'=>'leer'),'key3'=>'    ','keys2'=>array('key1'=>' ','key2'=> ' ')),
      array('keys'=>array('key1'=>NULL,'key2'=>'leer'),'key3'=>NULL,'keys2'=>array('key1'=>NULL,'key2'=>NULL))
                );
    
    $this->assertEquals(NULL,$data->getDataWithKeys(array('keys','key1')));
  }
  
  public function testWeirdComparison() {
    $ar = array();
    $data = new \Psc\DataInput($ar);
    
    /* im code war hier Vergleich von $do (int(1)) == self::THROW_EXCEPTION (string('throw'))
      ergibt TRUE
      
      da schmeisst das hier eine Exception, obwohl es default zurückgeben müsste
    */
    $this->assertEquals(0,$data->get(array('noneexistant'),0,0)); 
  }

  public function testGetDoAndDefault() {
    $data = $this->cons(
      array('keys'=>array('key1'=>' ','key2'=>'nichtleer'),'key3'=>'    ','keys2'=>array('key1'=>' ','key2'=> ' ')),
      array('keys'=>array('key1'=>NULL,'key2'=>'nichtleer'),'key3'=>NULL,'keys2'=>array('key1'=>NULL,'key2'=>NULL))
    );
    
    $this->assertEquals(NULL, $data->get(array('keys','key1'), 'do', NULL));
    $this->assertEquals(NULL, $data->get(array('keys','nonextistens'), DataInput::RETURN_NULL, NULL));
    
    $this->assertEquals('default', $data->get(array('keys','key1'), 'do', 'default'));
    $this->assertEquals('default', $data->get(array('key3'), 'do', 'default'));
    $this->assertEquals('do', $data->get(array('keys','notexistent'), 'do', 'default'));
    $this->assertEquals('do', $data->get('nothing', 'do', 'default'));
    $this->assertEquals('nichtleer', $data->get(array('keys','key2'), 'do', 'default'));
    $this->assertEquals(array('key1'=>NULL,'key2'=>NULL), $data->get('keys2', 'do', 'default'));
  }

  public function testThrow1() {
    $this->tdata->get('nonexists'); // default ist keine exception zu werfen
  }

  /**
   * @expectedException \Psc\DataInputException
   */
  public function testThrow2() {
    $this->tdata->get('nonexists', DataInput::THROW_EXCEPTION);
  }

  /**
   * @expectedException \Psc\DataInputException
   */
  public function testThrow3() {
    $this->tdata->get('nonexists', DataInput::THROW_EXCEPTION, DataInput::THROW_EXCEPTION);
  }
  
  /**
   * @expectedException \Psc\EmptyDataInputException
   */
  public function testThrowDefault1() {
    $this->assertEquals('do', $this->tdata->get('nonexists', 'do', DataInput::THROW_EXCEPTION));// keine
    $this->tdata->get('emptyKey', 'do', DataInput::THROW_EXCEPTION); // emptyData
  }


  /**
   * @expectedException \Psc\DataInputException
   */
  public function testThrowDefault2() {
    $this->assertEquals('default', $this->tdata->get('emptyKey', DataInput::THROW_EXCEPTION, 'default'));// keine
    $this->tdata->get('nonexistens', DataInput::THROW_EXCEPTION, 'default'); // Data
  }
  
  /**
   * @dataProvider provideTestRemove
   */
  public function testRemove($data, $keys, $existing = TRUE) {
    if ($existing) $this->assertTrue($data->contains($keys));
    $this->assertChainable($data->remove($keys));
    $this->assertFalse($data->contains($keys));
  }
  
  public static function provideTestRemove() {
    $tests = array();
    
    $ref = array('keys'=>array('key1'=>' ','key2'=>'leer'),'key3'=>'    ',
                 'keys2'=>array('key1'=>' ','key2'=> ' ','key3'=>array('key4'=>'nix'))
                );
    $data = new \Psc\DataInput($ref);
    
    // existing in array
    $tests[] = array($data, array('keys','key1'), TRUE );
    $tests[] = array($data, array('keys','key2'), TRUE );
    $tests[] = array($data, array('keys2','key3','key4'), TRUE );
    
    // non existing
    $tests[] = array($data, array('non'), FALSE );
    $tests[] = array($data, array('keys','keyxx'), FALSE );
    $tests[] = array($data, array('keys','key2','blubb'), FALSE );

    return $tests;    
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
  
  public function testMerge() {
    /* Project Paths */
    $conf['projects']['root'] = 'D:\www\\';
    $conf['projects']['tiptoi']['root'] = 'D:\www\RvtiptoiCMS\Umsetzung\\';
    $conf['projects']['SerienLoader']['root'] = 'D:\www\serien-loader\Umsetzung\\';

    /* Environment */
    $conf['defaults']['system']['timezone'] = 'Europe/Berlin';
    $conf['defaults']['system']['chmod'] = 0644;
    $conf['defaults']['i18n']['language'] = 'de';

    $hostData = new DataInput($conf);
    
    $pconf['system']['timezone'] = 'Europe/London';
    $pconf['projects']['SerienLoader']['root'] = 'D:\www\nothere';

    $projectData = new DataInput($pconf);
    
    $failMergedData = clone $hostData;
    $failMergedData->merge($projectData);
    
    $this->assertEquals('Europe/Berlin',$failMergedData->get('defaults.system.timezone'));
    $this->assertEquals('Europe/London',$failMergedData->get('system.timezone'));
    $this->assertEquals(NULL,$failMergedData->get('i18n.language'));
    
    $mergedData = clone $hostData;
    $mergedData->merge($projectData, array('defaults'));
    $this->assertEquals('Europe/London',$mergedData->get('system.timezone'));
    $this->assertEquals('D:\www\nothere',$mergedData->get('projects.SerienLoader.root'));
    
    // @TODO toKeys Case
  }
}