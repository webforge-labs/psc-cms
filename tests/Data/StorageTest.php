<?php

namespace Psc\Data;

use Psc\System\Dir;
use Psc\System\File;

/**
 * @group class:Psc\Data\Storage
 * @group cache
 */
class StorageTest extends \Psc\Code\Test\Base {
  
  protected $storageFile;
  
  public function setUp() {
    $this->storageFile = new File('storage.test.php',$this->getTestDirectory());
    $this->storageFile->delete();
  }
  
  public function testStorage() {
    $storage = new Storage(new PHPStorageDriver($this->storageFile));
    
    $data = array('eins'=>'dataeins',
              'drei'=>array('datadreiisarray'),
              'vier'=>7
            );
    
    $storage->setData($data);
    $storage->persist();
    
    $this->assertFileExists((string) $this->storageFile);
    unset($storage);
    
    /* Load Test */
    $persistedStorage = new Storage(new PHPStorageDriver($this->storageFile));
    $this->assertEquals($data, $persistedStorage->init()->getData()->toArray());
    
    /* modify Test */
    $persistedStorage->getData()->set('fuenf',5);
    $data['fuenf'] = 5;
    $this->assertEquals($data, $persistedStorage->getData()->toArray());
    
    $persistedStorage->persist();
    
    /* load modified Test */
    $modifiedStorage = new Storage(new PHPStorageDriver($this->storageFile));
    $this->assertEquals(array(), $modifiedStorage->getData()->toArray());
    $this->assertEquals($data, $modifiedStorage->init()->getData()->toArray());
  }
}

?>