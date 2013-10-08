<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\UploadManager
 */
class UploadManagerTest extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $manager;
  protected $dir;
  
  public function setUp() {
    $this->con = 'tests';
    $this->chainClass = 'Psc\CMS\UploadManager';
    parent::setUp();
    $this->manager = new UploadManager(
      NULL,
      $this->getDoctrinePackage(),
      UploadManager::createCache($this->dir = $this->getTestDirectory()->sub('tmp/'))
    );
    $this->dir->create()->wipe();
  }
  
  public function testStoreStoresFileInCacheAndCreatesNewEntity() {
    $description = 'Businessplan für Unternehmensgründung 2012';
    $fileEntity = $this->manager->store($cFile = $this->getCommonFile('Businessplan.pdf'), $description);
    
    $this->assertInstanceof('Psc\CMS\UploadedFile', $fileEntity);
    $this->assertFileEquals((string) $cFile, $fileEntity->getFile(),'Die Contents der Dateien sollten gleich sein');
    //$this->assertNotEmpty($fileEntity->getSourcePath(), 'sourcePath muss gesetzt sein');
    $this->assertEquals($description, $fileEntity->getDescription());
    $this->assertNotEmpty($fileEntity->getHash(),'hash muss gesetzt sein');
  }
  
  public function testEntityHasURLWithFakeEnding() {
    $this->resetDatabaseOnNextTest();
    $bp = $this->storeBusinessPlan();
    
    $this->assertStringEndsWith('/fake.pdf', $url = $bp->getUrl('fake.pdf'));
  }
  
  public function testFlushSavesEntityInDB() {
    $this->resetDatabaseOnNextTest();
    $newFileEntity = $this->storeBusinessPlan();

    // reload
    $fileEntity = $this->hydrate('File', $newFileEntity->getIdentifier());
    $this->assertNotSame($newFileEntity, $fileEntity);
    
    $this->assertEquals($newFileEntity->getHash(), $fileEntity->getHash());
    $this->assertEquals($newFileEntity->getDescription(), $fileEntity->getDescription());
    //$this->assertEquals($newFileEntity->getSourcePath(), $fileEntity->getSourcePath());
  }
  
  public function testManagerLoadsStoredEntityFromDBWithSeveralInput() {
    $this->resetDatabaseOnNextTest();
    $bp = $this->storeBusinessPlan();

    // laden per id
    $this->assertEquals($bp->getIdentifier(), $this->manager->load($bp->getIdentifier())->getIdentifier());
    // laden per hash
    $this->assertEquals($bp->getIdentifier(), $this->manager->load($bp->getHash())->getIdentifier());
    // laden per file
    $this->assertEquals($bp->getIdentifier(), $this->manager->load($this->getCommonFile('Businessplan.pdf'))->getIdentifier());
  }

  /**
   * Achtung: dies clear den em + den manager
   */
  protected function storeBusinessPlan() {
    $description = 'Businessplan für Unternehmensgründung 2012';
    $fileEntity = $this->manager->store($cFile = $this->getCommonFile('Businessplan.pdf'), $description);
    $this->manager->flush();
    $this->manager->clear();
    return $fileEntity;
  }
  
  
  public function managerLoadsEntityFromDBAndEntityReturnsAFileWhichHasTheSameContentsAsTheStoredOne() {
    $this->resetDatabaseOnNextTest();
    $this->storeBusinessPlan();
    
    // reload
    $fileEntity = $this->manager->load($fileEntity->getIdentifier());
    $this->assertInstanceOf('Psc\CMS\UploadedFile', $fileEntity);
    
    // files?
    $this->assertInstanceOf('Webforge\Common\System\File', $fileEntity->getFile());
    $this->assertFileEquals((string) $cFile, (string) $fileEntity->getFile());
  }
}
?>