<?php

class ConfigurationTest extends \PHPUnit_Framework_TestCase {
  
  protected $configuration;
  
  public function setUp() {
    $conf = array('var1'=>true,
          'var2'=>false,
          'root'=>'D:\www\ka',
          'var3'=>array('bananane')
        );
    
    $this->configuration = new \Psc\CMS\Configuration($conf);
  }

  /**
   * @expectedException \Psc\ConfigMissingVariableException
   */
  public function testMissingConfigException() {
    $this->configuration->req('habichnich');
  }
  
  /**
   * @depends testMissingConfigException
   */
  public function testKeysException() {
    try {
      $this->configuration->req(array('habichnich','bekommichnich'));
      
    } catch (\Psc\ConfigMissingVariableException $e) {
      $this->assertEquals(array('habichnich','bekommichnich'),$e->keys);
    }

    try {
      $this->configuration->req('habichnich.bekommichnich');
      
    } catch (\Psc\ConfigMissingVariableException $e) {
      $this->assertEquals(array('habichnich','bekommichnich'),$e->keys);
    }
  }
}