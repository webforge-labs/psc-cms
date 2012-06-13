<?php

namespace Psc\Data;

use \Psc\Data\APC;

/**
 * @group class:Psc\Data\APC
 * @group cache
 */
class APCTest extends \Psc\Data\CacheTestCase {

  protected function createCache() {
    return new APC();
  }
  
  public function testStoreAndLoadPrefix() {
    $this->cache = new APC('beliebigerPrefix');
    $this->cache->remove(array('eins'));
    $this->cache->remove(array('zwei'));

    $this->assertFalse($this->cache->hit(array('eins')));
    $this->assertFalse($this->cache->hit(array('zwei')));
    //$this->testStoreAndLoad();
  }
}
?>