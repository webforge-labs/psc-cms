<?php

namespace Psc\Data;

use \Psc\Data\PHPFileCache;

/**
 * @group cache
 */
class PHPFileCacheTest extends \Psc\Data\CacheTestCase {

  protected $cache;
  
  protected $eins;
  protected $zwei;
  
  protected $file;
  
  protected function createCache() {
    $storageFile = $this->newFile('storage.testcache.php');
    $storageFile->delete();
    $this->file = $storageFile;
    return new PHPFileCache($storageFile);
  }
  
  public function testPermanentStorage() {
    $this->assertFalse($this->cache->hit('p1'));
    
    $this->cache->store('p1','permanentValue1');
    $this->cache->persist();
    
    unset($this->cache);
    
    $this->cache = new PHPFileCache($this->file);
    $this->assertTrue($this->cache->hit('p1'));
    $this->assertEquals('permanentValue1',$this->cache->load('p1', $loaded));
  }
}
?>