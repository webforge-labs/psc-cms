<?php

namespace Psc\Data;

use \Psc\Data\ObjectCache;

/**
 * @group class:Psc\Data\ObjectCache
 * @group cache
 */
class ObjectCacheTest extends \Psc\Code\Test\Base {
  
  protected $cache;
  
  protected $eins;
  protected $zwei;
  
  
  public function setUp() {
    $this->cache = new ObjectCache(function ($object){
      return array($object->id);
    }, 60); // ttl irgendwas was nicht kürzer als die zeit ist die testStoreAndLoad braucht
    
    $this->zwei = (object) array('id'=>'zwei',
                           'data'=>'ich bin zwei'
                          );

    $this->eins = (object) array('id'=>'eins',
                           'data'=>'ich bin eins'
                          );
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
    $this->cache->store(NULL,$this->zwei);
    $this->assertEquals(array('zwei'),$this->cache->getKeys($this->zwei));
    
    $loaded = FALSE;
    $zwei = $this->cache->load(array('zwei'),$loaded);
    $this->assertTrue($loaded);
    $this->assertEquals($this->zwei, $zwei);
  }
  
  public function testTTL() {
    $ttl = 2;
    
    $this->cache = new ObjectCache(function ($object){
      return $object->id;
    }, $ttl);

    $this->assertFalse($this->cache->hit(array('zwei')));
    
    $this->cache->store(NULL,$this->zwei);
    $this->assertTrue($this->cache->hit(array('zwei')));
    
    /* jetzt müsste im cache die expire UNGEFÄHR time()+$ttl sein
       wenn wir jetzt $ttl+1 sekunden pennen, ist es auf jeden fall expired
    */
    sleep($ttl+1);
    $this->assertFalse($this->cache->hit(array('zwei')));
  }
}

?>