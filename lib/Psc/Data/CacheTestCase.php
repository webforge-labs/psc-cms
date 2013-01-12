<?php

namespace Psc\Data;

abstract class CacheTestCase extends \Psc\Code\Test\Base {

  protected $cache; // cache für den der Test aufgerufen wird
  
  protected $eins;
  protected $zwei;
  
  
  public function setUp() {
    $this->cache = $this->createCache();
    $this->assertInstanceOf('Psc\Data\Cache',$this->cache,'createCache muss eine Psc\Data\Cache Instanz zurückgeben!');
    
    $this->cache->remove(array('eins'));
    $this->cache->remove(array('zwei'));
    
    
    $this->zwei = (object) array('id'=>'zwei',
                           'data'=>'ich bin zwei'
                          );

    $this->eins = (object) array('id'=>'eins',
                           'data'=>'ich bin eins'
                          );
  }
  
  /**
   * Gibt den Cache des Tests zurück
   *
   */
  abstract protected function createCache();
  
  public function assertPreConditions() {
    $this->assertFalse($this->cache->hit(array('eins')));
    $this->assertFalse($this->cache->hit(array('zwei')));
  }
  
  /**
   * @expectedException \InvalidArgumentException
   */
  public function testInvalidKey() {
    $this->cache->store(NULL,'banane');
  }
  
  public function testStoreAndLoad() {
    $this->assertFalse($this->cache->hit(array('eins')));
    $loaded = FALSE;
    $this->cache->load(array('eins'),$loaded);
    $this->assertFalse($loaded);
    
    $this->cache->store(array('eins'), $this->eins);
    
    $this->assertTrue($this->cache->hit(array('eins')));
    $loaded = FALSE;
    $this->cache->load(array('eins'),$loaded);
    $this->assertTrue($loaded);
    
    $eins = $this->cache->load(array('eins'),$loaded);
    $this->assertEquals($this->eins, $eins);
    
    /* store ohne keys */
    $this->assertFalse($this->cache->hit(array('zwei')));
    $this->cache->store(array('zwei'),$this->zwei);
    
    $loaded = FALSE;
    $zwei = $this->cache->load(array('zwei'),$loaded);
    $this->assertTrue($this->cache->hit(array('zwei')));
    $this->assertTrue($loaded);
    $this->assertEquals($this->zwei, $zwei);
  }
}
?>