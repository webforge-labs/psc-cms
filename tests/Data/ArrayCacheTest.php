<?php

namespace Psc\Data;

use Psc\Data\ArrayCache;

/**
 * @group class:Psc\Data\ArrayCache
 * @group cache
 */
class ArrayCacheTest extends \Psc\Data\CacheTestCase {

  protected function createCache() {
    return new ArrayCache();
  }
}
?>