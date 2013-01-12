<?php

namespace Psc\Data;

use Webforge\Common\System\File;

class PHPFileCache extends \Psc\Object implements PermanentCache {
  
  protected $storage;
  
  protected $autoPersist = FALSE;
  
  public function __construct(File $file) {
    $this->setStorage(new Storage(new PHPStorageDriver($file)));
  }
  
  public function setStorage(Storage $storage) {
    $this->storage = $storage;
    $this->init();
    return $this;
  }
  
  /**
   *
   * @chainable
   * @param mixed $key wenn $key === NULL ist muss die implementierung entscheiden ob sie den Schlüssel für ein Objekt selbst versucht zu generieren oder eine Exception schmeisst
   * @return Cache
   */
  public function store($key, $value) {
    $key = $this->getKey($key);
    
    $this->storage->getData()->set($key, $value);
    
    if ($this->autoPersist)
      $this->persist();
    
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function load($key, &$loaded) {
    $key = $this->getKey($key);
    
    try {
      $value = $this->storage->getData()->get($key, \Psc\DataInput::THROW_EXCEPTION);
      $loaded = TRUE;
      return $value;
    
    } catch (\Psc\DataInputException $e) {
      $loaded = FALSE;
    }
  }
  
  /**
   * @return bool
   */
  public function hit($key) {
    $loaded = FALSE;
    $this->load($key,$loaded);
    return $loaded;
  }
  
  public function remove($key) {
    $this->storage->getData()->remove($key);
    return $this;
  }
  
  /**
   * Speichert alle Keys im unterliegenden Storage ab
   *
   * Wird diese Funktion nicht aufgerufen sind die Keys im PHPFileCache nicht beim nächsten Request erreichbar
   * siehe auch $autoPersist
   */
  public function persist() {
    $this->storage->persist();
  }
  
  public function init() {
    $this->storage->init();
  }
  
  protected function getKey($key) {
    if ($key === NULL || $key === '') throw new \InvalidArgumentException('Schlüssel muss entweder ein nichtleerer Array oder ein nichtleerer String sein');
    
    /* wir brauchen hier nicht weitercasten wir geben einfach ans datainput weiter */
    return $key;
  }
}
?>