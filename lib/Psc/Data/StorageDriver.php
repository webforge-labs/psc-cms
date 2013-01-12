<?php

namespace Psc\Data;

interface StorageDriver {
  
  public function persist(Storage $storage);
  
  public function load(Storage $storage);
  
}

?>